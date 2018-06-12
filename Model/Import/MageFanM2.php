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
 * Class MageFanM2
 * @package Mageplaza\Blog\Model\Import
 */
class MageFanM2 extends AbstractImport
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

        if ($this->_importPosts($data, $connection) && $data['type'] == 'mage_fan') {
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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _importPosts($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data["table_prefix"] . "magefan_blog_post`";
        $result = mysqli_query($connection, $sqlString);
        if ($result) {
            $this->_resetRecords();
            $postModel = $this->_postFactory->create();
            $this->_deleteCount = $this->_behaviour($postModel, $data);
            $oldPostIds = [];
            $importSource = $data['type'] . '-' . $data['database'];

            while ($post = mysqli_fetch_assoc($result)) {

                $createDate = (strtotime($post['creation_time']) > strtotime($this->date->date())) ? strtotime($this->date->date()) : strtotime($post['creation_time']);
                $modifyDate = strtotime($post['update_time']);
                $publicDate = strtotime($post['publish_time']);
                $content = $post['content'];
                if ($postModel->isImportedPost($importSource, $post['post_id'])) {
                    //update posts
                    if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $postModel->isDuplicateUrlKey($post['identifier']) != null) {
                        $where = ['post_id = ?' => (int)$postModel->isDuplicateUrlKey($post['identifier'])];
                        $this->_resourceConnection->getConnection()
                            ->update($this->_resourceConnection
                                ->getTableName('mageplaza_blog_post'), [
                                'name' => $post['title'],
                                'short_description' => $post['short_content'],
                                'post_content' => $content,
                                'image' => $post['featured_img'],
                                'created_at' => $createDate,
                                'updated_at' => $modifyDate,
                                'publish_date' => $publicDate,
                                'enabled' => (int)$post['is_active'],
                                'in_rss' => 0,
                                'allow_comment' => 1,
                                'store_ids' => $this->_storeManager->getStore()->getId(),
                                'meta_robots' => 'INDEX,FOLLOW',
                                'meta_keywords' => $post['meta_keywords'],
                                'meta_description' => $post['meta_description'],
                                'author_id' => (int)$post['author_id'],
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
                        //re-import existing posts
                        try {
                            $postModel->setData([
                                'name' => $post['title'],
                                'short_description' => $post['short_content'],
                                'post_content' => $content,
                                'url_key' => $post['identifier'],
                                'image' => $post['featured_img'],
                                'created_at' => $createDate,
                                'updated_at' => $modifyDate,
                                'publish_date' => $publicDate,
                                'enabled' => (int)$post['is_active'],
                                'in_rss' => 0,
                                'allow_comment' => 1,
                                'store_ids' => $this->_storeManager->getStore()->getId(),
                                'meta_robots' => 'INDEX,FOLLOW',
                                'meta_keywords' => $post['meta_keywords'],
                                'meta_description' => $post['meta_description'],
                                'author_id' => (int)$post['author_id'],
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
            }

            foreach ($postModel->getCollection() as $item) {
                if ($item->getImportSource() != null) {
                    $postImportSource = explode('-', $item->getImportSource());
                    $importType = array_shift($postImportSource);
                    if ($importType == 'mage_fan') {
                        $oldPostId = array_pop($postImportSource);
                        $oldPostIds[$item->getId()] = $oldPostId;
                    }
                }
            }
            mysqli_free_result($result);
            $statistics = $this->_getStatistics("posts", $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
            $this->_registry->register('mageplaza_import_post_ids_collection', $oldPostIds);
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
        $sqlString = "SELECT * FROM `" . $data["table_prefix"] . "magefan_blog_tag`";
        $result = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $oldTagIds = [];
        $tagModel = $this->_tagFactory->create();
        $this->_deleteCount = $this->_behaviour($tagModel, $data);
        $importSource = $data['type'] . '-' . $data['database'];
        while ($tag = mysqli_fetch_assoc($result)) {
            if ($tagModel->isImportedTag($importSource, $tag['tag_id'])) {
                //update tags
                if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $tagModel->isDuplicateUrlKey($tag['identifier']) != null) {
                    try {
                        $where = ['tag_id = ?' => (int)$tagModel->isDuplicateUrlKey($tag['identifier'])];
                        $this->_resourceConnection->getConnection()
                            ->update($this->_resourceConnection
                                ->getTableName('mageplaza_blog_tag'), [
                                'name' => $tag['title'],
                                'meta_robots' => 'INDEX,FOLLOW',
                                'meta_description' => $tag['meta_description'],
                                'meta_keywords' => $tag['meta_keywords'],
                                'meta_title' => $tag['meta_title'],
                                'store_ids' => $this->_storeManager->getStore()->getId(),
                                'enabled' => (int)$tag['is_active'],
                                'import_source' => $importSource . '-' . $tag['tag_id']
                            ], $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {
                    //re-import the existing tags
                    try {
                        $tagModel->setData([
                            'name' => $tag['title'],
                            'url_key' => $tag['identifier'],
                            'meta_robots' => 'INDEX,FOLLOW',
                            'meta_description' => $tag['meta_description'],
                            'meta_keywords' => $tag['meta_keywords'],
                            'meta_title' => $tag['meta_title'],
                            'store_ids' => $this->_storeManager->getStore()->getId(),
                            'enabled' => (int)$tag['is_active'],
                            'import_source' => $importSource . '-' . $tag['tag_id']
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
                if ($importType == 'mage_fan') {
                    $oldTagId = array_pop($tagImportSource);
                    $oldTagIds[$item->getId()] = $oldTagId;
                }
            }
        }

        //insert post tag relation
        $tagPostTable = $this->_resourceConnection->getTableName('mageplaza_blog_post_tag');
        $sqlTagPost = "SELECT * FROM " . $data["table_prefix"] . "`magefan_blog_post_tag` ";
        $result = mysqli_query($connection, $sqlTagPost);
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        while ($tagPost = mysqli_fetch_assoc($result)) {

            $newPostId = array_search($tagPost["post_id"], $oldPostIds);
            $newTagId = array_search($tagPost["tag_id"], $oldTagIds);
            try {
                $this->_resourceConnection->getConnection()->insert($tagPostTable, [
                    'tag_id' => $newTagId,
                    'post_id' => $newPostId,
                    'position' => 0
                ]);
            } catch (\Exception $e) {
                continue;
            }
        }
        mysqli_free_result($result);

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
        $sqlString = "SELECT * FROM `" . $data["table_prefix"] . "magefan_blog_category`";
        $result = mysqli_query($connection, $sqlString);
        $categoryModel = $this->_categoryFactory->create();
        $oldCategoryIds = [];
        $newCategories = [];
        $oldCategories = [];
        $this->_resetRecords();
        $this->_deleteCount = $this->_behaviour($categoryModel, $data, 1);
        $importSource = $data['type'] . '-' . $data['database'];
        while ($category = mysqli_fetch_assoc($result)) {

            if ($categoryModel->isImportedCategory($importSource, $category['category_id'])) {
                //update categories
                if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $categoryModel->isDuplicateUrlKey($category['identifier']) != null && $category['identifier'] != 'root') {
                    try {
                        $where = ['category_id = ?' => (int)$categoryModel->isDuplicateUrlKey($category['identifier'])];
                        $this->_resourceConnection->getConnection()
                            ->update($this->_resourceConnection
                                ->getTableName('mageplaza_blog_category'), [
                                'name' => $category['title'],
                                'meta_robots' => 'INDEX,FOLLOW',
                                'store_ids' => $this->_storeManager->getStore()->getId(),
                                'enabled' => (int)$category['is_active'],
                                'meta_description' => $category['meta_description'],
                                'meta_keywords' => $category['meta_keywords'],
                                'meta_title' => $category['meta_title'],
                                'import_source' => $importSource . '-' . $category['category_id']
                            ], $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {
                    //re-import the existing categories
                    try {
                        $categoryModel->setData([
                            'name' => $category['title'],
                            'url_key' => $category['identifier'],
                            'meta_robots' => 'INDEX,FOLLOW',
                            'store_ids' => $this->_storeManager->getStore()->getId(),
                            'enabled' => (int)$category['is_active'],
                            'path' => '1',
                            'meta_description' => $category['meta_description'],
                            'meta_keywords' => $category['meta_keywords'],
                            'meta_title' => $category['meta_title'],
                            'import_source' => $importSource . '-' . $category['category_id']
                        ])->save();
                        $newCategories[$categoryModel->getId()] = $category;
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                }
            }
            $oldCategories[$category['category_id']] = $category;
        }

        //store old category ids
        foreach ($categoryModel->getCollection() as $item) {
            if ($item->getImportSource() != null) {
                $catImportSource = explode('-', $item->getImportSource());
                $importType = array_shift($catImportSource);
                if ($importType == 'mage_fan') {
                    $oldCategoryId = array_pop($catImportSource);
                    $oldCategoryIds[$item->getId()] = $oldCategoryId;
                }
            }
        }

        //import parent-child category
        foreach ($newCategories as $newCatId => $newCategory) {
            if ($newCategory['path'] != '0' && $newCategory['path'] != null) {
                $oldParentId = explode("/", $newCategory['path']);
                $oldParentId = array_pop($oldParentId);
                $parentId = array_search($oldParentId, $oldCategoryIds);
                $parentPath = $categoryModel->load($parentId)->getPath();
                $parentPaths = explode('/', $categoryModel->getPath());
                $level = count($parentPaths);
                $newPath = $parentPath . '/' . $newCatId;
                $currentCategory = $categoryModel->load($newCatId);
                $currentCategory->setPath($newPath)->setParentId($parentId)->setLevel($level)->save();
            }
        }
        mysqli_free_result($result);

        //import category post relation
        $categoryPostTable = $this->_resourceConnection->getTableName('mageplaza_blog_post_category');
        $sqlCategoryPost = "SELECT * FROM " . $data["table_prefix"] . "`magefan_blog_post_category` ";
        $result = mysqli_query($connection, $sqlCategoryPost);
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        while ($categoryPost = mysqli_fetch_assoc($result)) {

            $newPostId = array_search($categoryPost["post_id"], $oldPostIds);
            $newTagId = array_search($categoryPost["category_id"], $oldCategoryIds);
            try {
                $this->_resourceConnection->getConnection()->insert($categoryPostTable, [
                    'category_id' => $newTagId,
                    'post_id' => $newPostId,
                    'position' => 0
                ]);
            } catch (\Exception $e) {
                continue;
            }
        }

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
        $sqlString = "SELECT * FROM `" . $data["table_prefix"] . "magefan_blog_comment` 
                      LEFT JOIN `" . $data["table_prefix"] . "customer_entity` 
                      ON `" . $data["table_prefix"] . "magefan_blog_comment`.`customer_id` = `" . $data["table_prefix"] . "customer_entity`.`entity_id`";
        $result = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $commentModel = $this->_commentFactory->create();
        $this->_deleteCount = $this->_behaviour($commentModel, $data);
        $customerModel = $this->_customerFactory->create();
        $websiteId = $this->_storeManager->getWebsite()->getId();
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $importSource = $data['type'] . '-' . $data['database'];
        while ($comment = mysqli_fetch_assoc($result)) {
            if ($commentModel->isImportedComment($importSource, $comment['comment_id'])) {
                $createDate = strtotime($comment['creation_time']);
                switch ($comment['status']) {
                    case '2':
                        $status = 2;
                        break;
                    case '1':
                        $status = 1;
                        break;
                    case '0':
                        $status = 3;
                        break;
                    default:
                        $status = 1;
                }
                $newPostId = array_search($comment['post_id'], $oldPostIds);
                $commentEmail = ($comment['customer_id'] == 0) ? $comment['author_email'] : $comment['email'];
                if ($accountManage->isEmailAvailable($commentEmail, $websiteId)) {
                    $entityId = 0;
                    $userName = $comment['author_nickname'];
                    $userEmail = $commentEmail;
                } else {
                    $customerModel->setWebsiteId($websiteId);
                    $customerModel->loadByEmail($commentEmail);
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
                        'content' => $comment['text'],
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
}
