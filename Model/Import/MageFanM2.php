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
     * Magefan Post table name
     *
     * @var string
     */
    const POST_TABLE = 'magefan_blog_post';

    /**
     * Magefan Related Post table name
     *
     * @var string
     */
    const POST_RELATED_TABLE = 'magefan_blog_post_relatedpost';

    /**
     * Magefan Tag table name
     *
     * @var string
     */
    const TAG_TABLE = 'magefan_blog_tag';

    /**
     * Magefan Post-Tag Relationship table name
     *
     * @var string
     */
    const POST_TAG_TABLE = 'magefan_blog_post_tag';

    /**
     * Magefan Category table name
     *
     * @var string
     */
    const CATEGORY_TABLE = 'magefan_blog_category';

    /**
     * Magefan Post-Category Relationship table name
     *
     * @var string
     */
    const POST_CATEGORY_TABLE = 'magefan_blog_post_category';

    /**
     * Magefan Comment table name
     *
     * @var string
     */
    const COMMENT_TABLE = 'magefan_blog_comment';

    /**
     * Magefan Customer table name
     *
     * @var string
     */
    const CUSTOMER_TABLE = 'customer_entity';

    /**
     * Magefan Admin user table name
     *
     * @var string
     */
    const ADMIN_USER_TABLE = 'admin_user';

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

        if ($this->_importPosts($data, $connection) && $data['type'] == $this->_type['magefan']) {
            $this->_importTags($data, $connection);
            $this->_importCategories($data, $connection);
            $this->_importComments($data, $connection);
            $this->_importAuthors($data, $connection);
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
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _importPosts($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::POST_TABLE . "`";
        $result = mysqli_query($connection, $sqlString);
        if ($result) {
            $this->_resetRecords();
            /** @var \Mageplaza\Blog\Model\PostFactory */
            $postModel = $this->_postFactory->create();
            $this->_deleteCount = $this->_behaviour($postModel, $data);
            $oldPostIds = [];
            $importSource = $data['type'] . '-' . $data['database'];

            while ($post = mysqli_fetch_assoc($result)) {

                /** check the posts is imported */
                if ($postModel->getResource()->isImported($importSource, $post['post_id'])) {
                    /** update post that has duplicate URK key */
                    if ($postModel->getResource()->isDuplicateUrlKey($post['identifier']) != null || $data['expand_behaviour'] == '1') {
                        $where = ['post_id = ?' => (int)$postModel->getResource()->isImported($importSource, $post['post_id'])];
                        $this->_updatePosts($postModel, $post, $importSource, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } else {
                        /** Add new posts */
                        $postModel->load($postModel->getResource()->isImported($importSource, $post['post_id']))->setImportSource('')->save();
                        try {
                            $this->_addPosts($postModel, $post, $importSource);
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
                    if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $postModel->getResource()->isDuplicateUrlKey($post['identifier']) != null) {
                        $where = ['post_id = ?' => (int)$postModel->getResource()->isDuplicateUrlKey($post['identifier'])];
                        $this->_updatePosts($postModel, $post, $importSource, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } else {
                        /**
                         * Add new posts
                         */
                        try {
                            $this->_addPosts($postModel, $post, $importSource);
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
            /**
             * Store old post ids
             */
            foreach ($postModel->getCollection() as $item) {
                if ($item->getImportSource() != null) {
                    $postImportSource = explode('-', $item->getImportSource());
                    $importType = array_shift($postImportSource);
                    if ($importType == $this->_type['magefan']) {
                        $oldPostId = array_pop($postImportSource);
                        $oldPostIds[$item->getId()] = $oldPostId;
                    }
                }
            }
            mysqli_free_result($result);

            /**
             * Insert topics
             */
            $topicSql = "SELECT post_id FROM " . $data['table_prefix'] . " `" . self::POST_RELATED_TABLE . "` GROUP BY post_id";
            $topicCount = 1;
            $oldTopicIds = [];

            /**
             * @var \Mageplaza\Blog\Model\TopicFactory
             */
            $topicModel = $this->_topicFactory->create();
            $this->_deleteCount = $this->_behaviour($topicModel, $data);
            $result = mysqli_query($connection, $topicSql);
            while ($topic = mysqli_fetch_assoc($result)) {
                if ($topicModel->getResource()->isImported($importSource, $topic['post_id']) == false) {
                    try {
                        $topicModel->setData([
                            'name' => 'magefan-topic-' . $topicCount,
                            'enabled' => 1,
                            'store_ids' => $this->_storeManager->getStore()->getId(),
                            'import_source' => $importSource . '-' . $topic['post_id']
                        ])->save();
                        $oldTopicIds[$topicModel->getId()] = $topic['post_id'];
                    } catch (\Exception $e) {
                        continue;
                    }
                    $topicCount++;
                }
            }
            mysqli_free_result($result);

            /**
             * Insert related posts
             */
            $topicPostTable = $this->_resourceConnection->getTableName('mageplaza_blog_post_topic');
            $topicPostSql = "SELECT * FROM " . $data['table_prefix'] . " `" . self::POST_RELATED_TABLE . "`";
            $result = mysqli_query($connection, $topicPostSql);
            while ($topicPost = mysqli_fetch_assoc($result)) {
                $newPostId = array_search($topicPost['related_id'], $oldPostIds);
                $newTopicId = array_search($topicPost['post_id'], $oldTopicIds);
                try {
                    $this->_resourceConnection->getConnection()->insert($topicPostTable, [
                        'topic_id' => $newTopicId,
                        'post_id' => $newPostId,
                        'position' => 0
                    ]);
                } catch (\Exception $e) {
                    continue;
                }
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

        /**
         * @var \Mageplaza\Blog\Model\TagFactory
         */
        $tagModel = $this->_tagFactory->create();
        $this->_deleteCount = $this->_behaviour($tagModel, $data);
        $importSource = $data['type'] . '-' . $data['database'];
        while ($tag = mysqli_fetch_assoc($result)) {
            if ($tagModel->getResource()->isImported($importSource, $tag['tag_id'])) {
                /** update tag that has duplicate URK key */
                if ($tagModel->getResource()->isDuplicateUrlKey($tag['identifier']) != null || $data['expand_behaviour'] == '1') {
                    try {
                        $where = ['tag_id = ?' => (int)$tagModel->getResource()->isImported($importSource, $tag['tag_id'])];
                        $this->_updateTags($tagModel, $tag, $importSource, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {
                    /** Add new tags */
                    $tagModel->load($tagModel->getResource()->isImported($importSource, $tag['tag_id']))->setImportSource('')->save();
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
                /**
                 * check the posts isn't imported
                 * Update tags
                 */
                if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $tagModel->getResource()->isDuplicateUrlKey($tag['identifier']) != null) {
                    try {
                        $where = ['tag_id = ?' => (int)$tagModel->getResource()->isDuplicateUrlKey($tag['identifier'])];
                        $this->_updateTags($tagModel, $tag, $importSource, $where);
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
                if ($importType == $this->_type['magefan']) {
                    $oldTagId = array_pop($tagImportSource);
                    $oldTagIds[$item->getId()] = $oldTagId;
                }
            }
        }

        /** Insert post tag relation */
        $tagPostTable = $this->_resourceConnection->getTableName('mageplaza_blog_post_tag');
        $sqlTagPost = "SELECT * FROM " . $data['table_prefix'] . "`" . self::POST_TAG_TABLE . "` ";
        $result = mysqli_query($connection, $sqlTagPost);
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        while ($tagPost = mysqli_fetch_assoc($result)) {
            $newPostId = array_search($tagPost['post_id'], $oldPostIds);
            $newTagId = array_search($tagPost['tag_id'], $oldTagIds);
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

        $statistics = $this->_getStatistics('tags', $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
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
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::CATEGORY_TABLE . "`";
        $result = mysqli_query($connection, $sqlString);
        /**
         * @var \Mageplaza\Blog\Model\CategoryFactory
         */
        $categoryModel = $this->_categoryFactory->create();
        $oldCategoryIds = [];
        $newCategories = [];
        $oldCategories = [];
        $this->_resetRecords();
        $this->_deleteCount = $this->_behaviour($categoryModel, $data, 1);
        $importSource = $data['type'] . '-' . $data['database'];
        while ($category = mysqli_fetch_assoc($result)) {
            if ($categoryModel->getResource()->isImported($importSource, $category['category_id'])) {
                /** update category that has duplicate URK key */
                if (($categoryModel->getResource()->isDuplicateUrlKey($category['identifier']) != null || $data['expand_behaviour'] == '1') && $category['identifier'] != 'root') {
                    try {
                        $where = ['category_id = ?' => (int)$categoryModel->getResource()->isImported($importSource, $category['category_id'])];
                        $this->_updateCategories($categoryModel, $category, $importSource, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } catch (\Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                } else {
                    /** Add new categories */
                    $categoryModel->load($categoryModel->getResource()->isImported($importSource, $category['category_id']))->setImportSource('')->save();
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
            } else {
                /**
                 * Update categories
                 */
                if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1' && $categoryModel->getResource()->isDuplicateUrlKey($category['identifier']) != null && $category['identifier'] != 'root') {
                    try {
                        $where = ['category_id = ?' => (int)$categoryModel->getResource()->isDuplicateUrlKey($category['identifier'])];
                        $this->_updateCategories($categoryModel, $category, $importSource, $where);
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
            }
            $oldCategories[$category['category_id']] = $category;
        }

        /**
         * Store old category ids
         */
        foreach ($categoryModel->getCollection() as $item) {
            if ($item->getImportSource() != null) {
                $catImportSource = explode('-', $item->getImportSource());
                $importType = array_shift($catImportSource);
                if ($importType == $this->_type['magefan']) {
                    $oldCategoryId = array_pop($catImportSource);
                    $oldCategoryIds[$item->getId()] = $oldCategoryId;
                }
            }
        }

        /** Import parent-child category */
        foreach ($newCategories as $newCatId => $newCategory) {
            if ($newCategory['path'] != '0' && $newCategory['path'] != null) {
                $oldParentId = explode('/', $newCategory['path']);
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

        /**
         * Import category post relation
         */
        $categoryPostTable = $this->_resourceConnection->getTableName('mageplaza_blog_post_category');
        $sqlCategoryPost = "SELECT * FROM " . $data['table_prefix'] . "`" . self::POST_CATEGORY_TABLE . "` ";
        $result = mysqli_query($connection, $sqlCategoryPost);
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        while ($categoryPost = mysqli_fetch_assoc($result)) {

            $newPostId = array_search($categoryPost['post_id'], $oldPostIds);
            $newTagId = array_search($categoryPost['category_id'], $oldCategoryIds);
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

        $statistics = $this->_getStatistics('categories', $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
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
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::COMMENT_TABLE . "` 
                      LEFT JOIN `" . $data['table_prefix'] . self::CUSTOMER_TABLE . "` 
                      ON `" . $data['table_prefix'] . self::COMMENT_TABLE . "`.`customer_id` = `" . $data['table_prefix'] . self::CUSTOMER_TABLE . "`.`entity_id`";
        $result = mysqli_query($connection, $sqlString);
        $this->_resetRecords();

        /** @var \Mageplaza\Blog\Model\CommentFactory */
        $commentModel = $this->_commentFactory->create();
        $oldCommentIds = [];
        $newComments = [];
        $this->_deleteCount = $this->_behaviour($commentModel, $data);
        $customerModel = $this->_customerFactory->create();
        $websiteId = $this->_storeManager->getWebsite()->getId();
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $importSource = $data['type'] . '-' . $data['database'];
        while ($comment = mysqli_fetch_assoc($result)) {
            /**
             * mapping status
             */
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
            /** search for new post id */
            $newPostId = array_search($comment['post_id'], $oldPostIds);
            $commentEmail = ($comment['customer_id'] == 0) ? $comment['author_email'] : $comment['email'];
            /** check if comment author is customer */
            if ($accountManage->isEmailAvailable($commentEmail, $websiteId)) {
                $entityId = 0;
                $userName = $comment['author_nickname'];
                $userEmail = $commentEmail;
            } else {
                /** comment author is guest */
                $customerModel->setWebsiteId($websiteId);
                $customerModel->loadByEmail($commentEmail);
                $entityId = $customerModel->getEntityId();
                $userName = '';
                $userEmail = '';
            }

            /** import actions */
            if ($commentModel->getResource()->isImported($importSource, $comment['comment_id'])) {
                /** update comments */
                $where = ['comment_id = ?' => (int)$commentModel->getResource()->isImported($importSource, $comment['comment_id'])];
                $this->_resourceConnection->getConnection()
                    ->update($this->_resourceConnection
                        ->getTableName('mageplaza_blog_comment'), [
                        'post_id' => $newPostId,
                        'entity_id' => $entityId,
                        'has_reply' => 0,
                        'is_reply' => 0,
                        'reply_id' => 0,
                        'content' => $comment['text'],
                        'created_at' => strtotime($comment['creation_time']),
                        'status' => $status,
                        'store_ids' => $this->_storeManager->getStore()->getId(),
                        'user_name' => $userName,
                        'user_email' => $userEmail,
                        'import_source' => $importSource . '-' . $comment['comment_id']
                    ], $where);
                $this->_successCount++;
                $this->_hasData = true;
            } else {
                /** add new comments */
                try {
                    $commentModel->setData([
                        'post_id' => $newPostId,
                        'entity_id' => $entityId,
                        'has_reply' => 0,
                        'is_reply' => 0,
                        'reply_id' => 0,
                        'content' => $comment['text'],
                        'created_at' => strtotime($comment['creation_time']),
                        'status' => $status,
                        'store_ids' => $this->_storeManager->getStore()->getId(),
                        'user_name' => $userName,
                        'user_email' => $userEmail,
                        'import_source' => $importSource . '-' . $comment['comment_id']
                    ])->save();
                    $newComments[$commentModel->getId()] = $comment;
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

        /** Store old comment ids */
        foreach ($commentModel->getCollection() as $item) {
            if ($item->getImportSource() != null) {
                $commentImportSource = explode('-', $item->getImportSource());
                $importType = array_shift($commentImportSource);
                if ($importType == $this->_type['magefan']) {
                    $oldCommentId = array_pop($commentImportSource);
                    $oldCommentIds[$item->getId()] = $oldCommentId;
                }
            }
        }

        /** Insert child-parent comments */
        foreach ($newComments as $newCommentId => $newComment) {
            if ($newComment['parent_id'] != '0') {
                $oldParentId = $newComment['parent_id'];
                $parentId = array_search($oldParentId, $oldCommentIds);
                $currentParentComment = $commentModel->load($parentId);
                if ($currentParentComment->getHasReply() == '0') {
                    $currentParentComment->setHasReply('1')->save();
                }
                $currentComment = $commentModel->load($newCommentId);
                $currentComment->setIsReply('1')->setReplyId($parentId)->save();
            }
        }

        $statistics = $this->_getStatistics('comments', $this->_successCount, $this->_errorCount, $this->_deleteCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_comment_statistic', $statistics);
    }

    /**
     * Import Author
     *
     * @param $data
     * @param $connection
     * @return mixed|void
     */
    protected function _importAuthors($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::ADMIN_USER_TABLE . "`";
        $result = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $oldUserIds = [];
        $magentoUserEmail = [];

        /** @var \Magento\User\Model\UserFactory */
        $userModel = $this->_userFactory->create();

        foreach ($userModel->getCollection() as $user) {
            $magentoUserEmail [] = $user->getEmail();
        }
        while ($user = mysqli_fetch_assoc($result)) {
            if (!in_array($user['email'], $magentoUserEmail)) {
                try {
                    $userModel->setData([
                        'username' => $user['username'],
                        'firstname' => $user['firstname'],
                        'lastname' => $user['lastname'],
                        'password' => $this->_generatePassword(12),
                        'email' => $user['email'],
                        'is_active' => (int)$user['is_active'],
                        'interface_locale' => $user['interface_locale'],
                        'created' => strtotime($user['created']),
                        'modified' => strtotime($user['modified']),
                        'logdate' => strtotime($user['logdate']),
                        'lognum' => (int)$user['lognum']
                    ])->setRoleId(1)->save();
                    $this->_successCount++;
                    $this->_hasData = true;
                    $oldUserIds[$userModel->getId()] = $user['user_id'];
                } catch (\Exception $e) {
                    $this->_errorCount++;
                    $this->_hasData = true;
                    continue;
                }
            } else {
                $oldUserIds[$user['user_id']] = $user['user_id'];
            }
        }
        mysqli_free_result($result);

        /**
         * Import post author relation
         */
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $updateData = [];
        foreach ($oldUserIds as $newUserId => $oldUserId) {
            $relationshipSql = "SELECT * FROM `" . $data['table_prefix'] . self::POST_TABLE . "` 
                                WHERE author_id = " . $oldUserId;
            $result = mysqli_query($connection, $relationshipSql);
            while ($postAuthor = mysqli_fetch_assoc($result)) {
                $newPostId = array_search($postAuthor['post_id'], $oldPostIds);
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
     * @param $postModel
     * @param $post
     * @param $importSource
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _addPosts($postModel, $post, $importSource)
    {
        $postModel->setData([
            'name' => $post['title'],
            'short_description' => $post['short_content'],
            'post_content' => $post['content'],
            'url_key' => $post['identifier'],
            'image' => $post['featured_img'],
            'created_at' => (strtotime($post['creation_time']) > strtotime($this->date->date())) ? strtotime($this->date->date()) : strtotime($post['creation_time']),
            'updated_at' => strtotime($post['update_time']),
            'publish_date' => strtotime($post['publish_time']),
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
    }

    /**
     * @param $postModel
     * @param $post
     * @param $importSource
     * @param $where
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _updatePosts($postModel, $post, $importSource, $where)
    {
        $this->_resourceConnection->getConnection()
            ->update($this->_resourceConnection
                ->getTableName('mageplaza_blog_post'), [
                'name' => $post['title'],
                'short_description' => $post['short_content'],
                'post_content' => $post['content'],
                'url_key' => $this->helperData->generateUrlKey($postModel->getResource(), $postModel, $post['identifier']),
                'image' => $post['featured_img'],
                'created_at' => (strtotime($post['creation_time']) > strtotime($this->date->date())) ? strtotime($this->date->date()) : strtotime($post['creation_time']),
                'updated_at' => strtotime($post['update_time']),
                'publish_date' => strtotime($post['publish_time']),
                'enabled' => (int)$post['is_active'],
                'in_rss' => 0,
                'allow_comment' => 1,
                'store_ids' => $this->_storeManager->getStore()->getId(),
                'meta_robots' => 'INDEX,FOLLOW',
                'meta_keywords' => $post['meta_keywords'],
                'meta_description' => $post['meta_description'],
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
    }

    /**
     * @param $tagModel
     * @param $tag
     * @param $importSource
     * @param $where
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _updateTags($tagModel, $tag, $importSource, $where)
    {

        $this->_resourceConnection->getConnection()
            ->update($this->_resourceConnection
                ->getTableName('mageplaza_blog_tag'), [
                'name' => $tag['title'],
                'url_key' => $this->helperData->generateUrlKey($tagModel->getResource(), $tagModel, $tag['identifier']),
                'meta_robots' => 'INDEX,FOLLOW',
                'meta_description' => $tag['meta_description'],
                'meta_keywords' => $tag['meta_keywords'],
                'meta_title' => $tag['meta_title'],
                'store_ids' => $this->_storeManager->getStore()->getId(),
                'enabled' => (int)$tag['is_active'],
                'import_source' => $importSource . '-' . $tag['tag_id']
            ], $where);
    }

    /**
     * @param $categoryModel
     * @param $category
     * @param $importSource
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _addCategories($categoryModel, $category, $importSource)
    {
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
    }

    /**
     * @param $categoryModel
     * @param $category
     * @param $importSource
     * @param $where
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _updateCategories($categoryModel, $category, $importSource, $where)
    {
        $this->_resourceConnection->getConnection()
            ->update($this->_resourceConnection
                ->getTableName('mageplaza_blog_category'), [
                'name' => $category['title'],
                'url_key' => $this->helperData->generateUrlKey($categoryModel->getResource(), $categoryModel, $category['identifier']),
                'meta_robots' => 'INDEX,FOLLOW',
                'store_ids' => $this->_storeManager->getStore()->getId(),
                'enabled' => (int)$category['is_active'],
                'meta_description' => $category['meta_description'],
                'meta_keywords' => $category['meta_keywords'],
                'meta_title' => $category['meta_title'],
                'import_source' => $importSource . '-' . $category['category_id']
            ], $where);
    }
}
