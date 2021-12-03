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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Model\Import;

use Exception;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\CommentFactory;
use Mageplaza\Blog\Model\Config\Source\Comments\Status;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\TagFactory;

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
    const TABLE_POST = 'aw_blog';
    /**
     * AheadworksM1 Tag table name
     *
     * @var string
     */
    const TABLE_TAG = 'aw_blog_tags';
    /**
     * AheadworksM1 Category table name
     *
     * @var string
     */
    const TABLE_CATEGORY = 'aw_blog_cat';
    /**
     * AheadworksM1 Comment table name
     *
     * @var string
     */
    const TABLE_COMMENT = 'aw_blog_comment';
    /**
     * AheadworksM1 Category-Post Relationship table name
     *
     * @var string
     */
    const TABLE_CATEGORY_POST = 'aw_blog_post_cat';

    /**
     * Run imports
     *
     * @param array $data
     * @param null $connection
     *
     * @return bool
     * @throws LocalizedException
     */
    public function run($data, $connection)
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        mysqli_query($connection, 'SET NAMES "utf8"');

        if ($this->_importPosts($data, $connection) && $data['type'] == $this->_type['aheadworksm1']) {
            $this->_importTags($data, $connection);
            $this->_importCategories($data, $connection);
            $this->_importComments($data, $connection);

            return true;
        }

        return false;
    }

    /**
     * @param array $data
     * @param null $connection
     *
     * @return bool|mixed
     * @throws LocalizedException
     */
    protected function _importPosts($data, $connection)
    {
        // phpcs:disable Magento2.SQL.RawQuery
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_POST . "`";
        $result    = mysqli_query($connection, $sqlString);
        $isReplace = true;
        if (!$result) {
            return false;
        }

        $this->_resetRecords();
        /** @var PostFactory $postModel */
        $postModel    = $this->_postFactory->create();
        $oldPostIds   = [];
        $tags         = [];
        $importSource = $data['type'] . '-' . $data['database'];

        /** delete behaviour action */
        if ($data['behaviour'] == 'delete' || $data['behaviour'] == 'replace') {
            $postModel->getResource()->deleteImportItems($data['type']);
            $this->_hasData = true;
            if ($data['behaviour'] == 'delete') {
                $isReplace = false;
            } else {
                $isReplace = true;
            }
        }

        /** fetch all items from import source */
        while ($post = mysqli_fetch_assoc($result)) {
            /** store the source item */
            $sourceItems[] = [
                'is_imported'       => $postModel->getResource()->isImported($importSource, $post['post_id']),
                'is_duplicated_url' => $postModel->getResource()->isDuplicateUrlKey($post['identifier']),
                'id'                => $post['post_id'],
                'name'              => $post['title'],
                'short_description' => $post['short_content'],
                'post_content'      => $post['post_content'],
                'url_key'           => $this->helperData->generateUrlKey(
                    $postModel->getResource(),
                    $postModel,
                    $post['identifier']
                ),
                'created_at'        => ($post['created_time'] > $this->date->date() || !$post['created_time'])
                    ? ($this->date->date()) : ($post['created_time']),
                'updated_at'        => ($post['update_time']) ?: $this->date->date(),
                'publish_date'      => ($post['created_time']) ?: $this->date->date(),
                'enabled'           => 1,
                'in_rss'            => 0,
                'allow_comment'     => 1,
                'store_ids'         => $this->_storeManager->getStore()->getId(),
                'meta_robots'       => 'INDEX,FOLLOW', //Default value
                'meta_keywords'     => $post['meta_keywords'],
                'meta_description'  => $post['meta_description'],
                'author_id'         => 1,
                'import_source'     => $importSource . '-' . $post['post_id'],
                'tags'              => $post['tags']
            ];
        }

        /** update and replace behaviour action */
        if ($isReplace && isset($sourceItems)) {
            foreach ($sourceItems as $post) {
                $postTag = $post['tags'];
                if ($post['is_imported']) {
                    /** update post that has duplicate URK key */
                    if ($post['is_duplicated_url'] != null || $data['expand_behaviour'] == '1') {
                        $where = ['post_id = ?' => (int) $post['is_imported']];
                        $this->_updatePosts($post, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } else {
                        /** Add new posts */
                        $postModel->load($post['is_imported'])->setImportSource('')->save();
                        try {
                            $this->_addPosts($postModel, $post);
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (Exception $e) {
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
                    if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1'
                        && $post['is_duplicated_url'] != null) {
                        $where = ['post_id = ?' => (int) $post['is_duplicated_url']];
                        $this->_updatePosts($post, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } else {
                        /**
                         * Add new posts
                         */
                        try {
                            $this->_addPosts($postModel, $post);
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (Exception $e) {
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
                    $id       = [];
                    foreach ($tagNames as $name) {
                        // phpcs:disable Magento2.Files.LineLength
                        $tagTableSql = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_TAG . "` WHERE `tag` = '" . $name . "'";
                        $tagResult   = mysqli_query($connection, $tagTableSql);
                        $tag         = mysqli_fetch_assoc($tagResult);
                        $id []       = $tag['id'];
                    }
                    if ($post['is_imported']) {
                        $tags[$post['is_imported']] = $id;
                    }
                }
            }

            /**
             * Get old post ids
             */
            foreach ($postModel->getCollection() as $item) {
                if ($item->getImportSource() != null) {
                    $postImportSource = explode('-', $item->getImportSource());
                    $importType       = array_shift($postImportSource);
                    if ($importType == $this->_type['aheadworksm1']) {
                        $oldPostId                  = array_pop($postImportSource);
                        $oldPostIds[$item->getId()] = $oldPostId;
                    }
                }
            }
            mysqli_free_result($result);
        }

        $statistics = $this->_getStatistics('posts', $this->_successCount, $this->_errorCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_post_ids_collection', $oldPostIds);
        $this->_registry->register('mageplaza_import_post_tags_collection', $tags);
        $this->_registry->register('mageplaza_import_post_statistic', $statistics);

        return true;
    }

    /**
     * @param array $data
     * @param null $connection
     *
     * @return mixed|void
     * @throws NoSuchEntityException
     */
    protected function _importTags($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_TAG . "`";
        $result    = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $isReplace = true;
        $oldTagIds = [];

        /** @var TagFactory $tagModel */
        $tagModel     = $this->_tagFactory->create();
        $importSource = $data['type'] . '-' . $data['database'];

        /** delete behaviour action */
        if ($data['behaviour'] == 'delete' || $data['behaviour'] == 'replace') {
            $tagModel->getResource()->deleteImportItems($data['type']);
            $this->_hasData = true;
            if ($data['behaviour'] == 'delete') {
                $isReplace = false;
            } else {
                $isReplace = true;
            }
        }

        /** fetch all items from import source */
        while ($tag = mysqli_fetch_assoc($result)) {
            /** store the source item */
            $sourceItems[] = [
                'is_imported'       => $tagModel->getResource()->isImported($importSource, $tag['id']),
                'is_duplicated_url' => $tagModel->getResource()->isDuplicateUrlKey($tag['tag']),
                'id'                => $tag['id'],
                'name'              => $tag['tag'],
                'meta_robots'       => 'INDEX,FOLLOW',
                'store_ids'         => $this->_storeManager->getStore()->getId(),
                'enabled'           => 1,
                'import_source'     => $importSource . '-' . $tag['id']
            ];
        }

        /** update and replace behaviour action */
        if ($isReplace && isset($sourceItems)) {
            foreach ($sourceItems as $tag) {
                if ($tag['is_imported']) {
                    /** update tag that has duplicate URK key */
                    if ($tag['is_duplicated_url'] != null || $data['expand_behaviour'] == '1') {
                        try {
                            $where = ['tag_id = ?' => (int) $tag['is_imported']];
                            $this->_updateTags($tag, $where);
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (Exception $e) {
                            $this->_errorCount++;
                            $this->_hasData = true;
                            continue;
                        }
                    } else {
                        /** Add new tags */
                        $tagModel->load($tag['is_imported'])->setImportSource('')->save();
                        try {
                            $this->_addTags($tagModel, $tag);
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (Exception $e) {
                            $this->_errorCount++;
                            $this->_hasData = true;
                            continue;
                        }
                    }
                } else {
                    /** Update tags */
                    if ($data['behaviour'] == 'update'
                        && $data['expand_behaviour'] == '1'
                        && $tag['is_duplicated_url'] != null) {
                        try {
                            $where = ['tag_id = ?' => (int) $tag['is_duplicated_url']];
                            $this->_updateTags($tag, $where);
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (Exception $e) {
                            $this->_errorCount++;
                            $this->_hasData = true;
                            continue;
                        }
                    } else {
                        /** Add new tags */
                        try {
                            $this->_addTags($tagModel, $tag);
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (Exception $e) {
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
                    $importType      = array_shift($tagImportSource);
                    if ($importType == $this->_type['aheadworksm1']) {
                        $oldTagId                  = array_pop($tagImportSource);
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
                            ->insert(
                                $this->_resourceConnection->getTableName('mageplaza_blog_post_tag'),
                                ['tag_id' => $newTagId, 'post_id' => $postId, 'position' => 0]
                            );
                    } catch (Exception $e) {
                        continue;
                    }
                }
            }
        }

        $statistics = $this->_getStatistics('tags', $this->_successCount, $this->_errorCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_tag_statistic', $statistics);
    }

    /**
     * @param array $data
     * @param null $connection
     *
     * @return mixed|void
     * @throws LocalizedException
     */
    protected function _importCategories($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data["table_prefix"] . self::TABLE_CATEGORY . "`";
        $result    = mysqli_query($connection, $sqlString);
        $isReplace = true;

        /** @var CategoryFactory $categoryModel */
        $categoryModel  = $this->_categoryFactory->create();
        $oldCategoryIds = [];
        $this->_resetRecords();
        $importSource = $data['type'] . '-' . $data['database'];

        /** delete behaviour action */
        if ($data['behaviour'] === 'delete' || $data['behaviour'] === 'replace') {
            $categoryModel->getResource()->deleteImportItems($data['type']);
            $this->_hasData = true;
            $isReplace      = !($data['behaviour'] === 'delete');
        }

        /** fetch all items from import source */
        while ($category = mysqli_fetch_assoc($result)) {
            /** store the source item */
            $sourceItems[] = [
                'is_imported'       => $categoryModel->getResource()->isImported($importSource, $category['cat_id']),
                'is_duplicated_url' => $categoryModel->getResource()->isDuplicateUrlKey($category['identifier']),
                'id'                => $category['cat_id'],
                'name'              => $category['title'],
                'url_key'           => $this->helperData->generateUrlKey(
                    $categoryModel->getResource(),
                    $categoryModel,
                    $category['identifier']
                ),
                'meta_robots'       => 'INDEX,FOLLOW',
                'store_ids'         => $this->_storeManager->getStore()->getId(),
                'enabled'           => 1,
                'path'              => '1',
                'meta_description'  => $category['meta_description'],
                'meta_keywords'     => $category['meta_keywords'],
                'import_source'     => $importSource . '-' . $category['cat_id']
            ];
        }

        /** update and replace behaviour action */
        if ($isReplace && isset($sourceItems)) {
            foreach ($sourceItems as $category) {
                if ($category['is_imported']) {
                    /** update category that has duplicate URK key */
                    if (($category['is_duplicated_url'] != null || $data['expand_behaviour'] == '1')
                        && $category['url_key'] != 'root') {
                        try {
                            $where = ['category_id = ?' => (int) $category['is_imported']];
                            $this->_updateCategories($category, $where);
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (Exception $e) {
                            $this->_errorCount++;
                            $this->_hasData = true;
                            continue;
                        }
                    } else {
                        /** Add new categories */
                        $categoryModel->load($category['is_imported'])->setImportSource('')->save();
                        try {
                            $this->_addCategories($categoryModel, $category);
                            $newCategories[$categoryModel->getId()] = $category;
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (Exception $e) {
                            $this->_errorCount++;
                            $this->_hasData = true;
                            continue;
                        }
                    }
                } else {
                    /**
                     * Update categories
                     */
                    if ($data['behaviour'] == 'update'
                        && $data['expand_behaviour'] == '1'
                        && $category['is_duplicated_url'] != null
                        && $category['url_key'] != 'root') {
                        try {
                            $where = ['category_id = ?' => (int) $category['is_duplicated_url']];
                            $this->_updateCategories($category, $where);
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (Exception $e) {
                            $this->_errorCount++;
                            $this->_hasData = true;
                            continue;
                        }
                    } else {
                        /**
                         * Add new categories
                         */
                        try {
                            $this->_addCategories($categoryModel, $category);
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (Exception $e) {
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
                    $importType      = array_shift($catImportSource);
                    if ($importType == $this->_type['aheadworksm1']) {
                        $oldCategoryId                  = array_pop($catImportSource);
                        $oldCategoryIds[$item->getId()] = $oldCategoryId;
                    }
                }
            }

            mysqli_free_result($result);

            $this->_importCategoryPost($data, $connection, $oldCategoryIds, 'mageplaza_blog_post_category');
        }

        $statistics = $this->_getStatistics("categories", $this->_successCount, $this->_errorCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_category_statistic', $statistics);
    }

    /**
     * Import comments
     *
     * @param array $data
     * @param null $connection
     *
     * @throws LocalizedException
     */
    protected function _importComments($data, $connection)
    {
        $accountManage = $this->_objectManager->create(AccountManagement::class);
        $sqlString     = "SELECT * FROM `" . $data["table_prefix"] . self::TABLE_COMMENT . "`";
        $result        = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $isReplace = true;
        /** @var CommentFactory $commentModel */
        $commentModel  = $this->_commentFactory->create();
        $customerModel = $this->_customerFactory->create();
        $websiteId     = $this->_storeManager->getWebsite()->getId();
        $oldPostIds    = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $importSource  = $data['type'] . '-' . $data['database'];

        /** delete behaviour action */
        if ($data['behaviour'] === 'delete' || $data['behaviour'] === 'replace') {
            $commentModel->getResource()->deleteImportItems($data['type']);
            $this->_hasData = true;
            $isReplace      = !($data['behaviour'] === 'delete');
        }

        /** fetch all items from import source */
        while ($comment = mysqli_fetch_assoc($result)) {
            /** mapping status */
            switch ($comment['status']) {
                case '2':
                    $status = Status::APPROVED;
                    break;
                case '1':
                    $status = Status::PENDING;
                    break;
                default:
                    $status = Status::APPROVED;
            }
            /** search for new post id */
            $newPostId = array_search($comment['post_id'], $oldPostIds);
            /** check if comment author is customer */
            if ($accountManage->isEmailAvailable($comment['email'], $websiteId)) {
                $entityId  = 0;
                $userName  = $comment['user'];
                $userEmail = $comment['email'];
            } else {
                /** comment author is guest */
                $customerModel->setWebsiteId($websiteId);
                $customerModel->loadByEmail($comment['email']);
                $entityId  = $customerModel->getEntityId();
                $userName  = '';
                $userEmail = '';
            }

            /** store the source item */
            $sourceItems[] = [
                'is_imported'   => $commentModel->getResource()->isImported($importSource, $comment['comment_id']),
                'id'            => $comment['comment_id'],
                'post_id'       => $newPostId,
                'entity_id'     => $entityId,
                'has_reply'     => 0,
                'is_reply'      => 0,
                'reply_id'      => 0,
                'content'       => $comment['comment'],
                'created_at'    => ($comment['created_time']) ?: $this->date->date(),
                'status'        => $status,
                'store_ids'     => $this->_storeManager->getStore()->getId(),
                'user_name'     => $userName,
                'user_email'    => $userEmail,
                'import_source' => $importSource . '-' . $comment['comment_id']
            ];
        }

        /** update and replace behaviour action */
        if ($isReplace && isset($sourceItems)) {
            foreach ($sourceItems as $comment) {
                /** import actions */
                if ($comment['is_imported']) {
                    /** update comments */
                    $where = ['comment_id = ?' => (int) $comment['is_imported']];
                    $this->_resourceConnection->getConnection()
                        ->update(
                            $this->_resourceConnection->getTableName('mageplaza_blog_comment'),
                            [
                                'post_id'       => $comment['post_id'],
                                'entity_id'     => $comment['entity_id'],
                                'has_reply'     => $comment['has_reply'],
                                'is_reply'      => $comment['is_reply'],
                                'reply_id'      => $comment['reply_id'],
                                'content'       => $comment['content'],
                                'created_at'    => $comment['created_at'],
                                'status'        => $comment['status'],
                                'store_ids'     => $comment['store_ids'],
                                'user_name'     => $comment['user_name'],
                                'user_email'    => $comment['user_email'],
                                'import_source' => $comment['import_source']
                            ],
                            $where
                        );
                    $this->_successCount++;
                    $this->_hasData = true;
                } else {
                    /** add new comments */
                    try {
                        $commentModel->setData([
                            'post_id'       => $comment['post_id'],
                            'entity_id'     => $comment['entity_id'],
                            'has_reply'     => $comment['has_reply'],
                            'is_reply'      => $comment['is_reply'],
                            'reply_id'      => $comment['reply_id'],
                            'content'       => $comment['content'],
                            'created_at'    => $comment['created_at'],
                            'status'        => $comment['status'],
                            'store_ids'     => $comment['store_ids'],
                            'user_name'     => $comment['user_name'],
                            'user_email'    => $comment['user_email'],
                            'import_source' => $comment['import_source']
                        ])->save();
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                }
            }
            mysqli_free_result($result);
        }

        $statistics = $this->_getStatistics('comments', $this->_successCount, $this->_errorCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_comment_statistic', $statistics);
    }

    // phpcs:disable Magento2.CodeAnalysis.EmptyBlock

    /**
     * @param array $data
     * @param null $connection
     *
     * @return mixed|void
     */
    protected function _importAuthors($data, $connection)
    {
        // TODO: Implement _importAuthors() method.
    }

    /**
     * Import category posts relationships
     *
     * @param array $data
     * @param null $connection
     * @param array $oldCatIds
     * @param string $relationTable
     */
    protected function _importCategoryPost($data, $connection, $oldCatIds, $relationTable)
    {
        $oldPostIds        = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $categoryPostTable = $this->_resourceConnection->getTableName($relationTable);
        foreach ($oldPostIds as $newPostId => $oldPostId) {
            $sqlRelation = "SELECT * FROM `" . $data["table_prefix"]
                . self::TABLE_CATEGORY_POST . "` WHERE `post_id` = " . $oldPostId;
            $result      = mysqli_query($connection, $sqlRelation);
            while ($categoryPost = mysqli_fetch_assoc($result)) {
                $newCategoryId = (array_search($categoryPost['cat_id'], $oldCatIds)) ?: '1';
                try {
                    $this->_resourceConnection->getConnection()->insert($categoryPostTable, [
                        'category_id' => $newCategoryId,
                        'post_id'     => $newPostId,
                        'position'    => 0
                    ]);
                } catch (Exception $e) {
                    continue;
                }
            }
        }
    }

    /**
     * Add posts to database
     *
     * @param PostFactory $postModel
     * @param array $post
     */
    private function _addPosts($postModel, $post)
    {
        $postModel->setData([
            'name'              => $post['name'],
            'short_description' => $post['short_description'],
            'post_content'      => $post['post_content'],
            'url_key'           => $post['url_key'],
            'created_at'        => $post['created_at'],
            'updated_at'        => $post['updated_at'],
            'publish_date'      => $post['publish_date'],
            'enabled'           => $post['enabled'],
            'in_rss'            => $post['in_rss'],
            'allow_comment'     => $post['allow_comment'],
            'store_ids'         => $post['store_ids'],
            'meta_robots'       => $post['meta_robots'],
            'meta_keywords'     => $post['meta_keywords'],
            'meta_description'  => $post['meta_description'],
            'author_id'         => $post['author_id'],
            'import_source'     => $post['import_source']
        ])->save();
    }

    /**
     * Update posts to database
     *
     * @param array $post
     * @param array $where
     */
    private function _updatePosts($post, $where)
    {
        $this->_resourceConnection->getConnection()->update(
            $this->_resourceConnection->getTableName('mageplaza_blog_post'),
            [
                'name'              => $post['name'],
                'short_description' => $post['short_description'],
                'post_content'      => $post['post_content'],
                'url_key'           => $post['url_key'],
                'created_at'        => $post['created_at'],
                'updated_at'        => $post['updated_at'],
                'publish_date'      => $post['publish_date'],
                'enabled'           => $post['enabled'],
                'in_rss'            => $post['in_rss'],
                'allow_comment'     => $post['allow_comment'],
                'store_ids'         => $post['store_ids'],
                'meta_robots'       => $post['meta_robots'],
                'meta_keywords'     => $post['meta_keywords'],
                'meta_description'  => $post['meta_description'],
                'author_id'         => $post['author_id'],
                'import_source'     => $post['import_source']
            ],
            $where
        );
        $this->_resourceConnection->getConnection()
            ->delete($this->_resourceConnection
                ->getTableName('mageplaza_blog_post_category'), $where);
        $this->_resourceConnection->getConnection()
            ->delete($this->_resourceConnection
                ->getTableName('mageplaza_blog_post_tag'), $where);
    }

    /**
     * @param TagFactory $tagModel
     * @param array $tag
     */
    private function _addTags($tagModel, $tag)
    {
        $tagModel->setData([
            'name'          => $tag['name'],
            'meta_robots'   => $tag['meta_robots'],
            'store_ids'     => $tag['store_ids'],
            'enabled'       => $tag['enabled'],
            'import_source' => $tag['import_source']
        ])->save();
    }

    /**
     * @param array $tag
     * @param array $where
     */
    private function _updateTags($tag, $where)
    {
        $this->_resourceConnection->getConnection()->update(
            $this->_resourceConnection->getTableName('mageplaza_blog_tag'),
            [
                'name'          => $tag['name'],
                'meta_robots'   => $tag['meta_robots'],
                'store_ids'     => $tag['store_ids'],
                'enabled'       => $tag['enabled'],
                'import_source' => $tag['import_source']
            ],
            $where
        );
    }

    /**
     * @param CategoryFactory $categoryModel
     * @param array $category
     */
    private function _addCategories($categoryModel, $category)
    {
        $categoryModel->setData([
            'name'             => $category['name'],
            'url_key'          => $category['url_key'],
            'meta_robots'      => $category['meta_robots'],
            'store_ids'        => $category['store_ids'],
            'enabled'          => $category['enabled'],
            'path'             => $category['path'],
            'meta_description' => $category['meta_description'],
            'meta_keywords'    => $category['meta_keywords'],
            'import_source'    => $category['import_source']
        ])->save();
    }

    /**
     * @param array $category
     * @param array $where
     */
    private function _updateCategories($category, $where)
    {
        $this->_resourceConnection->getConnection()->update(
            $this->_resourceConnection->getTableName('mageplaza_blog_category'),
            [
                'name'             => $category['name'],
                'url_key'          => $category['url_key'],
                'meta_robots'      => $category['meta_robots'],
                'store_ids'        => $category['store_ids'],
                'enabled'          => $category['enabled'],
                'meta_description' => $category['meta_description'],
                'meta_keywords'    => $category['meta_keywords'],
                'import_source'    => $category['import_source']
            ],
            $where
        );
    }
}
