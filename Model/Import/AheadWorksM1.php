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
     * @param $data
     * @param $connection
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function run($data, $connection)
    {
        mysqli_query($connection, 'SET NAMES "utf8"');

        if ($this->_importPosts($data, $connection) && $data['type'] == 'ahead_work_m1') {
            $this->_importTags($data, $connection);
            $this->_importCategories($data, $connection);
            $this->_importComments($data, $connection);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $data
     * @param $connection
     * @return bool|mixed
     */
    protected function _importPosts($data, $connection)
    {
        $authorId = $this->_authSession->getUser()->getId();
        $sqlString = "SELECT * FROM `" . $data["table_prefix"] . "aw_blog`";

        $result = mysqli_query($connection, $sqlString);
        if ($result) {
            $this->_resetRecords();
            $postModel = $this->_postFactory->create();
            $this->_deleteCount = $this->_behaviour($postModel, $data);
            $oldPostIds = [];
            $tags = [];
            $importSource = strtoupper($data['type']) . '-' . $data['database'];

            while ($post = mysqli_fetch_assoc($result)) {
                $createDate = (strtotime($post['created_time']) > strtotime($this->date->date())) ? strtotime($this->date->date()) : strtotime($post['created_time']);
                $modifyDate = strtotime($post['update_time']);
                $publicDate = strtotime($post['created_time']);
                $content = $post['post_content'];
                $postTag = $post['tags'];
                if ($postModel->isImportedPost($importSource, $post['post_id'])) {
                    if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $postModel->isDuplicateUrlKey($post['identifier']) != null) {
                        $where = ['post_id = ?' => (int)$postModel->isDuplicateUrlKey($post['identifier'])];
                        $this->_resourceConnection->getConnection()
                            ->update($this->_resourceConnection
                                ->getTableName('mageplaza_blog_post'), [
                                'name' => $post['title'],
                                'short_description' => $post['short_content'],
                                'post_content' => $content,
                                'created_at' => $createDate,
                                'updated_at' => $modifyDate,
                                'publish_date' => $publicDate,
                                "enabled" => 1,
                                'in_rss' => 0,
                                'allow_comment' => 1,
                                'store_ids' => $this->_storeManager->getStore()->getId(),
                                'meta_robots' => 'INDEX,FOLLOW',
                                'meta_keywords' => $post['meta_keywords'],
                                'meta_description' => $post['meta_description'],
                                'author_id' => (int)$authorId,
                                'import_source' => $importSource . '-' . $post['post_id']
                            ], $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                        $this->_resourceConnection->getConnection()
                            ->delete($this->_resourceConnection
                                ->getTableName('mageplaza_blog_post_category'), $where);
                        $this->_resourceConnection->getConnection()
                            ->delete($this->_resourceConnection
                                ->getTableName('mageplaza_blog_post_tag'), $where);
                    } else {
                        try {
                            $postModel->setData([
                                'name' => $post['title'],
                                'short_description' => $post['short_content'],
                                'post_content' => $content,
                                'url_key' => $post['identifier'],
                                'created_at' => $createDate,
                                'updated_at' => $modifyDate,
                                'publish_date' => $publicDate,
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
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (\Exception $e) {
                            $this->_errorCount++;
                            $this->_hasData = true;
                            continue;
                        }
                    }
                }
                if (!empty($postTag)) {
                    $tagNames = explode(",", $postTag);
                    $id = [];
                    foreach ($tagNames as $name) {
                        $tagTableSql = "SELECT * FROM `" . $data["table_prefix"] . "aw_blog_tags` WHERE `tag` = '" . $name . "'";
                        $tagResult = mysqli_query($connection, $tagTableSql);
                        $tag = mysqli_fetch_assoc($tagResult);
                        $id [] = $tag['id'];
                    }
                    $tags[$postModel->getPostIdByImportSource($importSource, $post['post_id'])] = $id;
                }
            }

            foreach ($postModel->getCollection() as $item) {
                if ($item->getImportSource() != null) {
                    $postImportSource = explode('-', $item->getImportSource());
                    $importType = array_shift($postImportSource);
                    if ($importType == 'AHEAD_WORK_M1') {
                        $oldPostId = array_pop($postImportSource);
                        $oldPostIds[$item->getId()] = $oldPostId;
                    }
                }
            }
            mysqli_free_result($result);
            $statistics = $this->_getStatistics("posts", $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
            $this->_registry->register('mageplaza_import_post_ids_collection', $oldPostIds);
            $this->_registry->register('mageplaza_import_post_tags_collection', $tags);
            $this->_registry->register('mageplaza_import_post_statistic', $statistics);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $data
     * @param $connection
     * @return mixed|void
     */
    protected function _importTags($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data["table_prefix"] . "aw_blog_tags`";
        $result = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $oldTagIds = [];
        $tagModel = $this->_tagFactory->create();
        $this->_deleteCount = $this->_behaviour($tagModel, $data);
        $importSource = strtoupper($data['type']) . '-' . $data['database'];
        while ($tag = mysqli_fetch_assoc($result)) {
            if ($tagModel->isImportedTag($importSource, $tag['id'])) {
                if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $tagModel->isDuplicateUrlKey($tag['tag']) != null) {
                    try {
                        $where = ['tag_id = ?' => (int)$tagModel->isDuplicateUrlKey($tag['tag'])];
                        $this->_resourceConnection->getConnection()
                            ->update($this->_resourceConnection
                                ->getTableName('mageplaza_blog_tag'), [
                                'name' => $tag['tag'],
                                'meta_robots' => 'INDEX,FOLLOW',
                                'store_ids' => $this->_storeManager->getStore()->getId(),
                                'enabled' => 1,
                                'import_source' => $importSource . '-' . $tag['id']
                            ], $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {
                    try {
                        $tagModel->setData([
                            'name' => $tag['tag'],
                            'meta_robots' => 'INDEX,FOLLOW',
                            'store_ids' => $this->_storeManager->getStore()->getId(),
                            'enabled' => 1,
                            'import_source' => $importSource . '-' . $tag['id']
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

        foreach ($tagModel->getCollection() as $item) {
            if ($item->getImportSource() != null) {
                $tagImportSource = explode('-', $item->getImportSource());
                $importType = array_shift($tagImportSource);
                if ($importType == 'AHEAD_WORK_M1') {
                    $oldTagId = array_pop($tagImportSource);
                    $oldTagIds[$item->getId()] = $oldTagId;
                }
            }
        }
        $tags = $this->_registry->registry('mageplaza_import_post_tags_collection');

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
     * @param $data
     * @param $connection
     * @return mixed|void
     */
    protected function _importCategories($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data["table_prefix"] . "aw_blog_cat`";
        $result = mysqli_query($connection, $sqlString);
        $categoryModel = $this->_categoryFactory->create();
        $oldCategoryIds = [];
        $this->_resetRecords();
        $this->_deleteCount = $this->_behaviour($categoryModel, $data, 1);
        $importSource = strtoupper($data['type']) . '-' . $data['database'];
        while ($category = mysqli_fetch_assoc($result)) {
            if ($categoryModel->isImportedCategory($importSource, $category['cat_id'])) {
                if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $categoryModel->isDuplicateUrlKey($category['identifier']) != null && $category['identifier'] != 'root') {
                    try {
                        $where = ['category_id = ?' => (int)$categoryModel->isDuplicateUrlKey($category['identifier'])];
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
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {
                    try {
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

        foreach ($categoryModel->getCollection() as $item) {
            if ($item->getImportSource() != null) {
                $catImportSource = explode('-', $item->getImportSource());
                $importType = array_shift($catImportSource);
                if ($importType == 'AHEAD_WORK_M1') {
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
     * @param $data
     * @param $connection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _importComments($data, $connection)
    {
        $accountManage = $this->_objectManager->create('\Magento\Customer\Model\AccountManagement');
        $sqlString = "SELECT * FROM `" . $data["table_prefix"] . "aw_blog_comment`";
        $result = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $commentModel = $this->_commentFactory->create();
        $this->_deleteCount = $this->_behaviour($commentModel, $data);
        $customerModel = $this->_customerFactory->create();
        $websiteId = $this->_storeManager->getWebsite()->getId();
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $importSource = strtoupper($data['type']) . '-' . $data['database'];
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
            $sqlRelation = "SELECT * FROM `" . $data["table_prefix"] . "aw_blog_post_cat` WHERE `post_id` = " . $oldPostId;
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

}
