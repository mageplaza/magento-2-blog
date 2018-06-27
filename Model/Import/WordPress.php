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
 * Class WordPress
 * @package Mageplaza\Blog\Model
 */
class WordPress extends AbstractImport
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

        /**
         * TODO: sua name cho chuan
         * @kenny
         */
        if ($this->_importPosts($data, $connection) && $data['type'] == 'word_press') {
            $this->_importTags($data, $connection);
            $this->_importCategories($data, $connection);
            $this->_importAuthors($data, $connection);
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
        /**
         * TODO: TABLE = CONTANT
         * @kenny
         */
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . "posts` WHERE post_type = 'post' AND post_status <> 'auto-draft'";
        $result = mysqli_query($connection, $sqlString);
        if ($result) {
            $this->_resetRecords();
            /**
             * @var \Mageplaza\Blog\Model\PostFactory
             */
            $postModel = $this->_postFactory->create();
            $this->_deleteCount = $this->_behaviour($postModel, $data);
            $oldPostIds = [];
            $importSource = $data['type'] . '-' . $data['database'];

            while ($post = mysqli_fetch_assoc($result)) {

                $createDate = (strtotime($post['post_date_gmt']) > strtotime($this->date->date())) ? strtotime($this->date->date()) : strtotime($post['post_date_gmt']);
                $modifyDate = strtotime($post['post_modified_gmt']);
                $publicDate = strtotime($post['post_date_gmt']);
                $content = $post['post_content'];
                /**
                 * TODO: doan nay phai huong dan How to copy media folder
                 */
                $content = preg_replace("/(http:\/\/)(.+?)(\/wp-content\/)/", $this->_helperImage->getBaseMediaUrl() . "/wysiwyg/", $content);
                $isEnable = ($post['post_status'] == 'trash') ? 0 : 1;
                if ($postModel->isImportedPost($importSource, $post['ID'])) {

                    /**
                     * Update posts
                     */
                    if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $postModel->isDuplicateUrlKey($post['post_name']) != null) {

                        $where = ['post_id = ?' => (int)$postModel->isDuplicateUrlKey($post['post_name'])];
                        $this->_resourceConnection->getConnection()
                            ->update($this->_resourceConnection
                                ->getTableName('mageplaza_blog_post'), [
                                'name' => $post['post_title'],
                                'short_description' => '',
                                'post_content' => $content,
                                'created_at' => $createDate,
                                'updated_at' => $modifyDate,
                                'publish_date' => $publicDate,
                                'enabled' => $isEnable,
                                'in_rss' => 0,
                                'allow_comment' => 1,
                                'store_ids' => $this->_storeManager->getStore()->getId(),
                                'meta_robots' => 'INDEX,FOLLOW',
                                'import_source' => $importSource . '-' . $post['ID']
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

                        /**
                         * replace existing posts
                         */
                        try {
                            $postModel->setData([
                                'name' => $post['post_title'],
                                'short_description' => '',
                                'post_content' => $content,
                                'url_key' => $post['post_name'],
                                'created_at' => $createDate,
                                'updated_at' => $modifyDate,
                                'publish_date' => $publicDate,
                                'enabled' => $isEnable,
                                'in_rss' => 0,
                                'allow_comment' => 1,
                                'store_ids' => $this->_storeManager->getStore()->getId(),
                                'meta_robots' => 'INDEX,FOLLOW',
                                'import_source' => $importSource . '-' . $post['ID']
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

                    /**
                     * TODO: sua name
                     * @kenny
                     */
                    if ($importType == 'word_press') {
                        $oldPostId = array_pop($postImportSource);
                        $oldPostIds[$item->getId()] = $oldPostId;
                    }
                }
            }
            /** Import post image banner */
            $oldPostMetaIds = [];
            $updateData = [];
            foreach ($oldPostIds as $newPostId => $oldPostId) {

                /**
                 * TODO: TABLE = CONTANT
                 * @kenny
                 */
                $postMetaSqlString = "SELECT * FROM `" . $data['table_prefix'] . "posts` WHERE `post_type` = 'attachment' and `post_parent` = '" . $oldPostId . "'";

                $result = mysqli_query($connection, $postMetaSqlString);
                if ($result) {
                    while ($postMeta = mysqli_fetch_assoc($result)) {
                        $oldPostMetaIds [$newPostId] = $postMeta['ID'];
                    }
                }
            }
            foreach ($oldPostMetaIds as $newPostId => $oldPostMetaId) {
                /**
                 * TODO: TABLE = CONTANT
                 * @kenny
                 */
                $postMetaSqlString = "SELECT * FROM `" . $data['table_prefix'] . "postmeta` WHERE `meta_key` = '_wp_attached_file' and `post_id` = '" . $oldPostMetaId . "'";
                $result = mysqli_query($connection, $postMetaSqlString);
                if ($result) {
                    while ($postMeta = mysqli_fetch_assoc($result)) {
                        $updateData [$newPostId] = 'uploads/' . $postMeta['meta_value'];
                    }
                }
            }
            foreach ($updateData as $postId => $postImage) {
                $where = ['post_id = ?' => (int)$postId];
                $this->_resourceConnection->getConnection()
                    ->update($this->_resourceConnection->getTableName('mageplaza_blog_post'), ['image' => $postImage], $where);
            }

            mysqli_free_result($result);

            $statistics = $this->_getStatistics('posts', $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
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
     */
    protected function _importTags($data, $connection)
    {
        /**
         * TODO: TABLE = CONTANT
         * @kenny
         */

        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . "terms` 
                          INNER JOIN `" . $data['table_prefix'] . "term_taxonomy` 
                          ON " . $data['table_prefix'] . "terms.term_id=" . $data['table_prefix'] . "term_taxonomy.term_id 
                          WHERE " . $data['table_prefix'] . "term_taxonomy.taxonomy = 'post_tag'";
        $result = mysqli_query($connection, $sqlString);
        $oldTagIds = [];
        $this->_resetRecords();

        /**
         * @var \Mageplaza\Blog\Model\TagFactory
         */
        $tagModel = $this->_tagFactory->create();
        $this->_deleteCount = $this->_behaviour($tagModel, $data);
        $importSource = $data['type'] . '-' . $data['database'];
        while ($tag = mysqli_fetch_assoc($result)) {
            if ($tagModel->isImportedTag($importSource, $tag['term_id'])) {

                /**
                 * Update tags
                 */
                if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $tagModel->isDuplicateUrlKey($tag['slug']) != null) {
                    try {
                        $where = ['tag_id = ?' => (int)$tagModel->isDuplicateUrlKey($tag['slug'])];
                        $this->_resourceConnection->getConnection()
                            ->update($this->_resourceConnection
                                ->getTableName('mageplaza_blog_tag'), [
                                'name' => $tag['name'],
                                'description' => $tag['description'],
                                'meta_robots' => 'INDEX,FOLLOW',
                                'store_ids' => $this->_storeManager->getStore()->getId(),
                                'enabled' => 1,
                                'import_source' => $importSource . '-' . $tag['term_id']
                            ], $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {

                    /**
                     * Re-import the existing tags
                     */
                    try {
                        $tagModel->setData([
                            'name' => $tag['name'],
                            'url_key' => $tag['slug'],
                            'description' => $tag['description'],
                            'meta_robots' => 'INDEX,FOLLOW',
                            'store_ids' => $this->_storeManager->getStore()->getId(),
                            'enabled' => 1,
                            'import_source' => $importSource . '-' . $tag['term_id']
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

        /**
         * Store old tag ids
         */
        foreach ($tagModel->getCollection() as $item) {
            if ($item->getImportSource() != null) {
                $tagImportSource = explode('-', $item->getImportSource());
                $importType = array_shift($tagImportSource);
                if ($importType == 'word_press') {
                    $oldTagId = array_pop($tagImportSource);
                    $oldTagIds[$item->getId()] = $oldTagId;
                }
            }
        }
        $this->_importRelationships($data, $connection, $oldTagIds, 'mageplaza_blog_post_tag', 'post_tag');

        $statistics = $this->_getStatistics('tags', $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_tag_statistic', $statistics);

    }

    /**
     * @param $data
     * @param $connection
     */
    protected function _importCategories($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . "terms` 
                          INNER JOIN `" . $data['table_prefix'] . "term_taxonomy` 
                          ON " . $data['table_prefix'] . "terms.term_id=" . $data['table_prefix'] . "term_taxonomy.term_id 
                          WHERE " . $data['table_prefix'] . "term_taxonomy.taxonomy = 'category' 
                          AND " . $data['table_prefix'] . "terms.name <> 'uncategorized' ";
        $result = mysqli_query($connection, $sqlString);

        /** @var \Mageplaza\Blog\Model\CategoryFactory */
        $categoryModel = $this->_categoryFactory->create();
        $newCategories = [];
        $oldCategories = [];
        $oldCategoryIds = [];
        $this->_resetRecords();
        $this->_deleteCount = $this->_behaviour($categoryModel, $data, 1);
        $importSource = $data['type'] . '-' . $data['database'];
        while ($category = mysqli_fetch_assoc($result)) {
            if ($categoryModel->isImportedCategory($importSource, $category['term_id'])) {

                /**
                 * Update categories
                 */
                if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $categoryModel->isDuplicateUrlKey($category['slug']) != null && $category['slug'] != 'root') {
                    try {
                        $where = ['category_id = ?' => (int)$categoryModel->isDuplicateUrlKey($category['slug'])];
                        $this->_resourceConnection->getConnection()
                            ->update($this->_resourceConnection
                                ->getTableName('mageplaza_blog_category'), [
                                'name' => $category['name'],
                                'description' => $category['description'],
                                'meta_robots' => 'INDEX,FOLLOW',
                                'store_ids' => $this->_storeManager->getStore()->getId(),
                                'enabled' => 1,
                                'import_source' => $importSource . '-' . $category['term_id']
                            ], $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {

                    /**
                     * Re-import the existing categories
                     */
                    try {
                        $categoryModel->setData([
                            'name' => $category['name'],
                            'url_key' => $category['slug'],
                            'description' => $category['description'],
                            'meta_robots' => 'INDEX,FOLLOW',
                            'store_ids' => $this->_storeManager->getStore()->getId(),
                            'enabled' => 1,
                            'path' => '1',
                            'import_source' => $importSource . '-' . $category['term_id']
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
            $oldCategories[$category['term_id']] = $category;
        }

        /**
         * Store old category ids
         */
        foreach ($categoryModel->getCollection() as $item) {
            if ($item->getImportSource() != null) {
                $catImportSource = explode('-', $item->getImportSource());
                $importType = array_shift($catImportSource);
                if ($importType == 'word_press') {
                    $oldCategoryId = array_pop($catImportSource);
                    $oldCategoryIds[$item->getId()] = $oldCategoryId;
                }
            }
        }

        /**
         * Import parent-child category
         */
        foreach ($newCategories as $newCatId => $newCategory) {
            if ($newCategory['parent'] != '0') {
                if (isset($oldCategories[$newCategory['parent']])) {
                    $parentId = array_search($newCategory['parent'], $oldCategoryIds);
                    $parentPath = $categoryModel->load($parentId)->getPath();
                    $parentPaths = explode('/', $categoryModel->getPath());
                    $level = count($parentPaths);
                    $newPath = $parentPath . '/' . $newCatId;
                    $currentCategory = $categoryModel->load($newCatId);
                    $currentCategory->setPath($newPath)->setParentId($parentId)->setLevel($level)->save();
                }
            }
        }
        mysqli_free_result($result);
        $this->_importRelationships($data, $connection, $oldCategoryIds, 'mageplaza_blog_post_category', 'category', 1);

        $statistics = $this->_getStatistics('categories', $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_category_statistic', $statistics);
    }

    /**
     * Import authors
     * @param $data
     * @param $connection
     */
    protected function _importAuthors($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . "users`";
        $result = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $oldUserIds = [];
        $magentoUserEmail = [];

        /**
         * @var \Magento\User\Model\UserFactory
         */
        $userModel = $this->_userFactory->create();

        foreach ($userModel->getCollection() as $user) {
            $magentoUserEmail [] = $user->getEmail();
        }
        while ($user = mysqli_fetch_assoc($result)) {
            if (!in_array($user['user_email'], $magentoUserEmail)) {
                $createDate = strtotime($user['user_registered']);
                try {
                    $userModel->setData([
                        'username' => $user['user_login'],
                        'firstname' => 'WP-',
                        'lastname' => $user['display_name'],
                        'password' => $this->_generatePassword(12),
                        'email' => $user['user_email'],
                        'is_active' => 1,
                        'interface_locale' => 'en_US',
                        'created' => $createDate
                    ])->setRoleId(1)->save();
                    $this->_successCount++;
                    $this->_hasData = true;
                    $oldUserIds[$userModel->getId()] = $user['ID'];

                } catch (\Exception $e) {
                    $this->_errorCount++;
                    $this->_hasData = true;
                    continue;
                }
            } else {
                $oldUserIds[$user['ID']] = $user['ID'];
            }
        }

        mysqli_free_result($result);

        /**
         * Import post author relation
         */
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $updateData = [];
        foreach ($oldUserIds as $newUserId => $oldUserId) {
            $relationshipSql = "SELECT ID FROM `" . $data['table_prefix'] . "posts` 
                                  WHERE post_author = " . $oldUserId . " 
                                  AND post_type = 'post' 
                                  AND post_status <> 'auto-draft'";
            $result = mysqli_query($connection, $relationshipSql);
            while ($postAuthor = mysqli_fetch_assoc($result)) {
                $newPostId = array_search($postAuthor['ID'], $oldPostIds);
                $updateData[$newPostId] = $newUserId;
            }
        }
        foreach ($updateData as $postId => $authorId) {
            $where = ['post_id = ?' => (int)$postId];
            $this->_resourceConnection->getConnection()
                ->update($this->_resourceConnection->getTableName('mageplaza_blog_post'), ['author_id' => $authorId], $where);
        }
        mysqli_free_result($result);
        $statistics = $this->_getStatistics('authors', $this->_successCount, $this->_errorCount, 0, $this->_hasData);
        $this->_registry->register('mageplaza_import_user_statistic', $statistics);
    }

    /**
     * Import comments
     * @param $data
     * @param $connection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _importComments($data, $connection)
    {
        $accountManage = $this->_objectManager->create('\Magento\Customer\Model\AccountManagement');
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . "comments`";
        $result = mysqli_query($connection, $sqlString);
        $this->_resetRecords();

        /**
         * @var \Mageplaza\Blog\Model\CommentFactory
         */
        $commentModel = $this->_commentFactory->create();
        $this->_deleteCount = $this->_behaviour($commentModel, $data);
        $customerModel = $this->_customerFactory->create();
        $websiteId = $this->_storeManager->getWebsite()->getId();
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $oldCommentIds = [];
        $importSource = $data['type'] . '-' . $data['database'];
        while ($comment = mysqli_fetch_assoc($result)) {
            if ($commentModel->isImportedComment($importSource, $comment['comment_ID'])) {
                $createDate = strtotime($comment['comment_date_gmt']);
                switch ($comment['comment_approved']) {
                    case '1':
                        $status = 1;
                        break;
                    case '0':
                        $status = 2;
                        break;
                    case 'spam':
                        $status = 3;
                        break;
                    default:
                        $status = 1;

                }
                $newPostId = array_search($comment['comment_post_ID'], $oldPostIds);
                if ($accountManage->isEmailAvailable($comment['comment_author_email'], $websiteId)) {
                    $entityId = 0;
                    $userName = $comment['comment_author'];
                    $userEmail = $comment['comment_author_email'];
                } else {
                    $customerModel->setWebsiteId($websiteId);
                    $customerModel->loadByEmail($comment['comment_author_email']);
                    $entityId = $customerModel->getEntityId();
                    $userName = '';
                    $userEmail = '';
                }
                try {
                    $commentModel->setData([
                        'post_id' => $newPostId,
                        'entity_id' => $entityId,
                        'has_reply' => 0,
                        'is_reply' => 0,
                        'reply_id' => 0,
                        'content' => $comment['comment_content'],
                        'created_at' => $createDate,
                        'status' => $status,
                        'store_ids' => $this->_storeManager->getStore()->getId(),
                        'user_name' => $userName,
                        'user_email' => $userEmail,
                        'import_source' => $importSource . '-' . $comment['comment_ID']
                    ])->save();
                    $this->_successCount++;
                    $this->_hasData = true;
                    $oldCommentIds [$commentModel->getId()] = $comment['comment_ID'];

                } catch (\Exception $e) {
                    $this->_errorCount++;
                    $this->_hasData = true;
                    continue;
                }
            }
        }

        $upgradeParentData = [];
        $upgradeChildData = [];

        /**
         * Insert child-parent comments
         */
        foreach ($oldCommentIds as $newCommentId => $oldCommentId) {
            $relationshipSql = "SELECT `comment_ID`,`comment_parent` FROM `" . $data['table_prefix'] . "comments` WHERE `comment_parent` <> 0";
            $result = mysqli_query($connection, $relationshipSql);

            while ($commentParent = mysqli_fetch_assoc($result)) {
                $newCommentParentId = array_search($commentParent['comment_parent'], $oldCommentIds);
                $newCommentChildId = array_search($commentParent['comment_ID'], $oldCommentIds);
                $upgradeChildData[$newCommentChildId] = $newCommentParentId;
                $upgradeParentData[$newCommentParentId] = 1;
            }
        }
        foreach ($upgradeChildData as $commentId => $commentParentId) {
            $where = ['comment_id = ?' => (int)$commentId];
            $this->_resourceConnection->getConnection()
                ->update($this->_resourceConnection
                    ->getTableName('mageplaza_blog_comment'), ['reply_id' => $commentParentId], $where);
        }
        foreach ($upgradeParentData as $commentId => $commentHasReply) {
            $where = ['comment_id = ?' => (int)$commentId];
            $this->_resourceConnection->getConnection()
                ->update($this->_resourceConnection
                    ->getTableName('mageplaza_blog_comment'), ['has_reply' => $commentHasReply], $where);
        }
        mysqli_free_result($result);
        $statistics = $this->_getStatistics('comments', $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_comment_statistic', $statistics);
    }

    /**
     * @param $data
     * @param $connection
     * @param $oldTermIds
     * @param $relationTable
     * @param $termType
     * @param null $isCategory
     */
    protected function _importRelationships($data, $connection, $oldTermIds, $relationTable, $termType, $isCategory = null)
    {
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $categoryPostTable = $this->_resourceConnection->getTableName($relationTable);
        foreach ($oldPostIds as $newPostId => $oldPostId) {
            $sqlRelation = "SELECT `" . $data['table_prefix'] . "term_taxonomy`.`term_id` 
                      FROM `" . $data['table_prefix'] . "term_taxonomy` 
                      INNER JOIN `" . $data['table_prefix'] . "term_relationships` 
                      ON " . $data['table_prefix'] . "term_taxonomy.`term_taxonomy_id`=" . $data['table_prefix'] . "term_relationships.`term_taxonomy_id` 
                      RIGHT JOIN `" . $data['table_prefix'] . "terms` 
                      ON " . $data['table_prefix'] . "term_taxonomy.`term_id` = " . $data['table_prefix'] . "terms.`term_id`
                      WHERE " . $data['table_prefix'] . "term_taxonomy.taxonomy = '" . $termType . "' 
                      AND `" . $data['table_prefix'] . "term_relationships`.`object_id` = " . $oldPostId;
            $result = mysqli_query($connection, $sqlRelation);
            while ($categoryPost = mysqli_fetch_assoc($result)) {
                if ($isCategory) {
                    $newCategoryId = (array_search($categoryPost['term_id'], $oldTermIds)) ?: '1';
                    $termId = 'category_id';
                } else {
                    $newCategoryId = array_search($categoryPost['term_id'], $oldTermIds);
                    $termId = 'tag_id';
                }
                try {
                    $this->_resourceConnection->getConnection()->insert($categoryPostTable, [
                        $termId => $newCategoryId,
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
