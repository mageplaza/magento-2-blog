<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Model\Import;

/**
 * Class AheadWorksM1
 * @package Mageplaza\Blog\Model\Import
 */
class AheadWorksM1 extends AbstractImport
{
    /**
     * AheadworksM1 Post table name
     *
     * @var string
     */
    const POST_TABLE = 'aw_blog';

    /**
     * AheadworksM1 Tag table name
     *
     * @var string
     */
    const TAG_TABLE = 'aw_blog_tags';

    /**
     * AheadworksM1 Category table name
     *
     * @var string
     */
    const CATEGORY_TABLE = 'aw_blog_cat';

    /**
     * AheadworksM1 Comment table name
     *
     * @var string
     */
    const COMMENT_TABLE = 'aw_blog_comment';

    /**
     * AheadworksM1 Category-Post Relationship table name
     *
     * @var string
     */
    const CATEGORY_POST_TABLE = 'aw_blog_post_cat';

    /**
     * Run imports
     *
     * @param $data
     * @param $connection
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function run($data, $connection)
    {
        mysqli_query($connection, 'SET NAMES "utf8"');

        if ($this->_importPosts($data, $connection) && $data['type'] == $this->_type['aheadworksm1']) {
            $this->_importTags($data, $connection);
            $this->_importCategories($data, $connection);
            $this->_importComments($data, $connection);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Import posts
     *
     * @param $data
     * @param $connection
     * @return bool|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _importPosts($data, $connection)
    {
        $authorId = $this->_authSession->getUser()->getId();
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::POST_TABLE . "`";
        $result = mysqli_query($connection, $sqlString);
        if ($result) {
            $this->_resetRecords();
            /**
             * @var \Mageplaza\Blog\Model\PostFactory
             */
            $postModel = $this->_postFactory->create();
            $this->_deleteCount = $this->_behaviour($postModel, $data);
            $oldPostIds = [];
            $tags = [];
            $importSource = $data['type'] . '-' . $data['database'];
            while ($post = mysqli_fetch_assoc($result)) {
                $postTag = $post['tags'];
                if ($postModel->isImportedPost($importSource, $post['post_id'])) {
                    /** update post that has duplicate URK key */
                    if ($postModel->isDuplicateUrlKey($post['identifier']) != null || $data['expand_behaviour'] == '1') {
                        $where = ['post_id = ?' => (int)$postModel->isImportedPost($importSource, $post['post_id'])];
                        $this->_updatePosts($post, $importSource, $authorId, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } else {
                        /** Add new posts */
                        $postModel->load($postModel->isImportedPost($importSource, $post['post_id']))->setImportSource('')->save();
                        try {
                            $this->_addPosts($postModel, $post, $importSource, $authorId);
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (\Exception $e) {
                            $this->_errorCount++;
                            $this->_hasData = true;
                            continue;
                        }
                    }
                } else {
                    /**
                     * check the posts isn't imported
                     * Update posts
                     */
                    if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $postModel->isDuplicateUrlKey($post['identifier']) != null) {
                        $where = ['post_id = ?' => (int)$postModel->isDuplicateUrlKey($post['identifier'])];
                        $this->_updatePosts($post, $importSource, $authorId, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } else {
                        /**
                         * Add new posts
                         */
                        try {
                            $this->_addPosts($postModel, $post, $importSource, $authorId);
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (\Exception $e) {
                            $this->_errorCount++;
                            $this->_hasData = true;
                            continue;
                        }
                    }
                }

                /**
                 * Store post-tags
                 */
                if (!empty($postTag)) {
                    $tagNames = explode(',', $postTag);
                    $id = [];
                    foreach ($tagNames as $name) {
                        $tagTableSql = "SELECT * FROM `" . $data['table_prefix'] . self::TAG_TABLE . "` WHERE `tag` = '" . $name . "'";
                        $tagResult = mysqli_query($connection, $tagTableSql);
                        $tag = mysqli_fetch_assoc($tagResult);
                        $id [] = $tag['id'];
                    }
                    $tags[$postModel->getPostIdByImportSource($importSource, $post['post_id'])] = $id;
                }
            }

            /**
             * Get old post ids
             */
            foreach ($postModel->getCollection() as $item) {
                if ($item->getImportSource() != null) {
                    $postImportSource = explode('-', $item->getImportSource());
                    $importType = array_shift($postImportSource);
                    if ($importType == $this->_type['aheadworksm1']) {
                        $oldPostId = array_pop($postImportSource);
                        $oldPostIds[$item->getId()] = $oldPostId;
                    }
                }
            }
            mysqli_free_result($result);
            $statistics = $this->_getStatistics('posts', $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
            $this->_registry->register('mageplaza_import_post_ids_collection', $oldPostIds);
            $this->_registry->register('mageplaza_import_post_tags_collection', $tags);
            $this->_registry->register('mageplaza_import_post_statistic', $statistics);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Import tags
     *
     * @param $data
     * @param $connection
     * @return mixed|void
     */
    protected function _importTags($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TAG_TABLE . "`";
        $result = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $oldTagIds = [];

        /** @var \Mageplaza\Blog\Model\TagFactory */
        $tagModel = $this->_tagFactory->create();
        $this->_deleteCount = $this->_behaviour($tagModel, $data);
        $importSource = $data['type'] . '-' . $data['database'];
        while ($tag = mysqli_fetch_assoc($result)) {
            if ($tagModel->isImportedTag($importSource, $tag['id'])) {
                /** update tag that has duplicate URK key */
                if ($tagModel->isDuplicateUrlKey($tag['tag']) != null || $data['expand_behaviour'] == '1') {
                    try {
                        $where = ['tag_id = ?' => (int)$tagModel->isImportedTag($importSource, $tag['id'])];
                        $this->_updateTags($tag, $importSource, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {
                    /** Add new tags */
                    $tagModel->load($tagModel->isImportedTag($importSource, $tag['id']))->setImportSource('')->save();
                    try {
                        $this->_addTags($tagModel, $tag, $importSource);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                }
            } else {

                /** Update tags */
                if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $tagModel->isDuplicateUrlKey($tag['tag']) != null) {
                    try {
                        $where = ['tag_id = ?' => (int)$tagModel->isDuplicateUrlKey($tag['tag'])];
                        $this->_updateTags($tag, $importSource, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {

                    /** Add new tags */
                    try {
                        $this->_addTags($tagModel, $tag, $importSource);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                }
            }
        }
        mysqli_free_result($result);

        /** Store old tag ids */
        foreach ($tagModel->getCollection() as $item) {
            if ($item->getImportSource() != null) {
                $tagImportSource = explode('-', $item->getImportSource());
                $importType = array_shift($tagImportSource);
                if ($importType == $this->_type['aheadworksm1']) {
                    $oldTagId = array_pop($tagImportSource);
                    $oldTagIds[$item->getId()] = $oldTagId;
                }
            }
        }
        $tags = $this->_registry->registry('mageplaza_import_post_tags_collection');

        /** Insert post tag relations */
        foreach ($tags as $postId => $tagIds) {
            foreach ($tagIds as $id) {
                try {
                    $newTagId = array_search($id, $oldTagIds);
                    $this->_resourceConnection->getConnection()
                        ->insert($this->_resourceConnection->getTableName('mageplaza_blog_post_tag'), ['tag_id' => $newTagId, 'post_id' => $postId, 'position' => 0]);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        $statistics = $this->_getStatistics("tags", $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_tag_statistic', $statistics);
    }

    /**
     * Import categories
     *
     * @param $data
     * @param $connection
     * @return mixed|void
     */
    protected function _importCategories($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data["table_prefix"] . self::CATEGORY_TABLE . "`";
        $result = mysqli_query($connection, $sqlString);

        /**
         * @var \Mageplaza\Blog\Model\CategoryFactory
         */
        $categoryModel = $this->_categoryFactory->create();
        $oldCategoryIds = [];
        $this->_resetRecords();
        $this->_deleteCount = $this->_behaviour($categoryModel, $data, 1);
        $importSource = $data['type'] . '-' . $data['database'];
        while ($category = mysqli_fetch_assoc($result)) {
            if ($categoryModel->isImportedCategory($importSource, $category['cat_id'])) {
                /** update category that has duplicate URK key */
                if (($categoryModel->isDuplicateUrlKey($category['identifier']) != null || $data['expand_behaviour'] == '1') && $category['identifier'] != 'root') {
                    try {
                        $where = ['category_id = ?' => (int)$categoryModel->isImportedCategory($importSource, $category['cat_id'])];
                        $this->_updateCategories($category, $importSource, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {
                    /** Add new categories */
                    $categoryModel->load($categoryModel->isImportedCategory($importSource, $category['category_id']))->setImportSource('')->save();
                    try {
                        $this->_addCategories($categoryModel, $category, $importSource);
                        $newCategories[$categoryModel->getId()] = $category;
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                }
            }else{

                /**
                 * Update categories
                 */
                if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $categoryModel->isDuplicateUrlKey($category['identifier']) != null && $category['identifier'] != 'root') {
                    try {
                        $where = ['category_id = ?' => (int)$categoryModel->isDuplicateUrlKey($category['identifier'])];
                        $this->_updateCategories($category,$importSource,$where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {

                    /**
                     * Add new categories
                     */
                    try {
                        $this->_addCategories($categoryModel,$category,$importSource);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                }
            }
        }

        /** Store old category ids */
        foreach ($categoryModel->getCollection() as $item) {
            if ($item->getImportSource() != null) {
                $catImportSource = explode('-', $item->getImportSource());
                $importType = array_shift($catImportSource);
                if ($importType == $this->_type['aheadworksm1']) {
                    $oldCategoryId = array_pop($catImportSource);
                    $oldCategoryIds[$item->getId()] = $oldCategoryId;
                }
            }
        }

        mysqli_free_result($result);

        $this->_importCategoryPost($data, $connection, $oldCategoryIds, 'mageplaza_blog_post_category');

        $statistics = $this->_getStatistics("categories", $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_category_statistic', $statistics);
    }

    /**
     * Import comments
     *
     * @param $data
     * @param $connection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _importComments($data, $connection)
    {
        $accountManage = $this->_objectManager->create('\Magento\Customer\Model\AccountManagement');
        $sqlString = "SELECT * FROM `" . $data["table_prefix"] . self::COMMENT_TABLE . "`";
        $result = mysqli_query($connection, $sqlString);
        $this->_resetRecords();

        /** @var \Mageplaza\Blog\Model\CommentFactory */
        $commentModel = $this->_commentFactory->create();
        $this->_deleteCount = $this->_behaviour($commentModel, $data);
        $customerModel = $this->_customerFactory->create();
        $websiteId = $this->_storeManager->getWebsite()->getId();
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $importSource = $data['type'] . '-' . $data['database'];
        while ($comment = mysqli_fetch_assoc($result)) {
            if ($commentModel->isImportedComment($importSource, $comment['comment_id'])) {
                $createDate = strtotime($comment['created_time']);
                switch ($comment['status']) {
                    case '2':
                        $status = 1;
                        break;
                    case '1':
                        $status = 3;
                        break;
                    default:
                        $status = 1;
                }
                $newPostId = array_search($comment['post_id'], $oldPostIds);
                if ($accountManage->isEmailAvailable($comment['email'], $websiteId)) {
                    $entityId = 0;
                    $userName = $comment['user'];
                    $userEmail = $comment['email'];
                } else {
                    $customerModel->setWebsiteId($websiteId);
                    $customerModel->loadByEmail($comment['email']);
                    $entityId = $customerModel->getEntityId();
                    $userName = "";
                    $userEmail = "";
                }

                /** import actions */
                if ($commentModel->isImportedComment($importSource, $comment['comment_id'])) {
                    /** update comments */
                    $where = ['comment_id = ?' => (int)$commentModel->isImportedComment($importSource, $comment['comment_id'])];
                    $this->_resourceConnection->getConnection()
                        ->update($this->_resourceConnection
                            ->getTableName('mageplaza_blog_comment'), [
                            'post_id' => $newPostId,
                            'entity_id' => $entityId,
                            'has_reply' => 0,
                            'is_reply' => 0,
                            'reply_id' => 0,
                            'content' => $comment['comment'],
                            'created_at' => $createDate,
                            'status' => $status,
                            'store_ids' => $this->_storeManager->getStore()->getId(),
                            'user_name' => $userName,
                            'user_email' => $userEmail,
                            'import_source' => $importSource . '-' . $comment['comment_id']
                        ], $where);
                    $this->_successCount++;
                    $this->_hasData = true;
                }else{
                    /** add new comments */
                    try {
                        $commentModel->setData([
                            'post_id' => $newPostId,
                            'entity_id' => $entityId,
                            'has_reply' => 0,
                            'is_reply' => 0,
                            'reply_id' => 0,
                            'content' => $comment['comment'],
                            'created_at' => $createDate,
                            'status' => $status,
                            'store_ids' => $this->_storeManager->getStore()->getId(),
                            'user_name' => $userName,
                            'user_email' => $userEmail,
                            'import_source' => $importSource . '-' . $comment['comment_id']
                        ])->save();
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                }
            }
        }
        mysqli_free_result($result);

        $statistics = $this->_getStatistics('comments', $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_comment_statistic', $statistics);
    }

    /**
     * @param $data
     * @param $connection
     * @return mixed|void
     */
    protected function _importAuthors($data, $connection)
    {
        // TODO: Implement _importAuthors() method.
    }

    /**
     * Import category posts relationships
     *
     * @param $data
     * @param $connection
     * @param $oldCatIds
     * @param $relationTable
     */
    protected function _importCategoryPost($data, $connection, $oldCatIds, $relationTable)
    {
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $categoryPostTable = $this->_resourceConnection->getTableName($relationTable);
        foreach ($oldPostIds as $newPostId => $oldPostId) {
            $sqlRelation = "SELECT * FROM `" . $data["table_prefix"] . self::CATEGORY_POST_TABLE . "` WHERE `post_id` = " . $oldPostId;
            $result = mysqli_query($connection, $sqlRelation);
            while ($categoryPost = mysqli_fetch_assoc($result)) {
                $newCategoryId = (array_search($categoryPost['cat_id'], $oldCatIds)) ?: '1';
                try {
                    $this->_resourceConnection->getConnection()->insert($categoryPostTable, [
                        'category_id' => $newCategoryId,
                        'post_id' => $newPostId,
                        'position' => 0
                    ]);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }

    /**
     * @param $postModel
     * @param $post
     * @param $importSource
     * @param $authorId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _addPosts($postModel, $post, $importSource, $authorId)
    {
        $postModel->setData([
            'name' => $post['title'],
            'short_description' => $post['short_content'],
            'post_content' => $post['post_content'],
            'url_key' => $post['identifier'],
            'created_at' => (strtotime($post['created_time']) > strtotime($this->date->date())) ? strtotime($this->date->date()) : strtotime($post['created_time']),
            'updated_at' => strtotime($post['update_time']),
            'publish_date' => strtotime($post['created_time']),
            'enabled' => 1,
            'in_rss' => 0,
            'allow_comment' => 1,
            'store_ids' => $this->_storeManager->getStore()->getId(),
            'meta_robots' => 'INDEX,FOLLOW',
            'meta_keywords' => $post['meta_keywords'],
            'meta_description' => $post['meta_description'],
            'author_id' => (int)$authorId,
            'import_source' => $importSource . '-' . $post['post_id']
        ])->save();
    }

    /**
     * @param $post
     * @param $importSource
     * @param $authorId
     * @param $where
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _updatePosts($post, $importSource, $authorId, $where)
    {
        $this->_resourceConnection->getConnection()
            ->update($this->_resourceConnection
                ->getTableName('mageplaza_blog_post'), [
                'name' => $post['title'],
                'short_description' => $post['short_content'],
                'post_content' => $post['post_content'],
                'created_at' => (strtotime($post['created_time']) > strtotime($this->date->date())) ? strtotime($this->date->date()) : strtotime($post['created_time']),
                'updated_at' => strtotime($post['update_time']),
                'publish_date' => strtotime($post['created_time']),
                "enabled" => 1,
                'in_rss' => 0,
                'allow_comment' => 1,
                'store_ids' => $this->_storeManager->getStore()->getId(),
                'meta_robots' => 'INDEX,FOLLOW', //Default value
                'meta_keywords' => $post['meta_keywords'],
                'meta_description' => $post['meta_description'],
                'author_id' => (int)$authorId,
                'import_source' => $importSource . '-' . $post['post_id']
            ], $where);
        $this->_resourceConnection->getConnection()
            ->delete($this->_resourceConnection
                ->getTableName('mageplaza_blog_post_category'), $where);
        $this->_resourceConnection->getConnection()
            ->delete($this->_resourceConnection
                ->getTableName('mageplaza_blog_post_tag'), $where);
    }

    /**
     * @param $tagModel
     * @param $tag
     * @param $importSource
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _addTags($tagModel, $tag, $importSource)
    {
        $tagModel->setData([
            'name' => $tag['tag'],
            'meta_robots' => 'INDEX,FOLLOW',
            'store_ids' => $this->_storeManager->getStore()->getId(),
            'enabled' => 1,
            'import_source' => $importSource . '-' . $tag['id']
        ])->save();
    }

    /**
     * @param $tag
     * @param $importSource
     * @param $where
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _updateTags($tag, $importSource, $where)
    {
        $this->_resourceConnection->getConnection()
            ->update($this->_resourceConnection
                ->getTableName('mageplaza_blog_tag'), [
                'name' => $tag['tag'],
                'meta_robots' => 'INDEX,FOLLOW',
                'store_ids' => $this->_storeManager->getStore()->getId(),
                'enabled' => 1,
                'import_source' => $importSource . '-' . $tag['id']
            ], $where);
    }

    /**
     * @param $categoryModel
     * @param $category
     * @param $importSource
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _addCategories($categoryModel,$category,$importSource)
    {
        $categoryModel->setData([
            'name' => $category['title'],
            'url_key' => $category['identifier'],
            'meta_robots' => 'INDEX,FOLLOW',
            'store_ids' => $this->_storeManager->getStore()->getId(),
            'enabled' => 1,
            'path' => '1',
            'meta_description' => $category['meta_description'],
            'meta_keywords' => $category['meta_keywords'],
            'import_source' => $importSource . '-' . $category['cat_id']
        ])->save();
    }

    /**
     * @param $category
     * @param $importSource
     * @param $where
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _updateCategories($category,$importSource,$where)
    {
        $this->_resourceConnection->getConnection()
            ->update($this->_resourceConnection
                ->getTableName('mageplaza_blog_category'), [
                'name' => $category['title'],
                'meta_robots' => 'INDEX,FOLLOW',
                'store_ids' => $this->_storeManager->getStore()->getId(),
                'enabled' => 1,
                'meta_description' => $category['meta_description'],
                'meta_keywords' => $category['meta_keywords'],
                'import_source' => $importSource . '-' . $category['cat_id']
            ], $where);
    }
}
