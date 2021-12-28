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
use Magento\Catalog\Model\Category;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Model\Author;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\Comment;
use Mageplaza\Blog\Model\Config\Source\AuthorType;
use Mageplaza\Blog\Model\Config\Source\Comments\Status;
use Mageplaza\Blog\Model\Post;
use Mageplaza\Blog\Model\Tag;
use Mageplaza\Blog\Model\Topic;

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
    const TABLE_POST = 'magefan_blog_post';

    /**
     * Magefan Related Post table name
     *
     * @var string
     */
    const TABLE_POST_RELATED = 'magefan_blog_post_relatedpost';

    /**
     * Magefan Tag table name
     *
     * @var string
     */
    const TABLE_TAG = 'magefan_blog_tag';

    /**
     * Magefan Post-Tag Relationship table name
     *
     * @var string
     */
    const TABLE_POST_TAG = 'magefan_blog_post_tag';

    /**
     * Magefan Category table name
     *
     * @var string
     */
    const TABLE_CATEGORY = 'magefan_blog_category';

    /**
     * Magefan Post-Category Relationship table name
     *
     * @var string
     */
    const TABLE_POST_CATEGORY = 'magefan_blog_post_category';

    /**
     * Magefan Comment table name
     *
     * @var string
     */
    const TABLE_COMMENT = 'magefan_blog_comment';

    /**
     * Magefan Customer table name
     *
     * @var string
     */
    const TABLE_CUSTOMER = 'customer_entity';

    /**
     * Magefan Admin user table name
     *
     * @var string
     */
    const TABLE_ADMIN_USER = 'admin_user';

    /**
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

        if ($this->_importPosts($data, $connection) && $data['type'] == $this->_type['magefan']) {
            $this->_importTags($data, $connection);
            $this->_importCategories($data, $connection);
            $this->_importComments($data, $connection);
            $this->_importAuthors($data, $connection);

            return true;
        }

        return false;
    }

    /**
     * Import posts
     *
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
        /** @var Post $postModel */
        $postModel = $this->_postFactory->create();
        /** @var Topic $topicModel */
        $topicModel   = $this->_topicFactory->create();
        $oldPostIds   = [];
        $importSource = $data['type'] . '-' . $data['database'];

        /** delete behaviour action */
        if ($data['behaviour'] === 'delete' || $data['behaviour'] === 'replace') {
            $postModel->getResource()->deleteImportItems($data['type']);
            $topicModel->getResource()->deleteImportItems($data['type']);
            $this->_hasData = true;
            $isReplace      = ($data['behaviour'] !== 'delete');
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
                'post_content'      => $post['content'],
                'url_key'           => $this->helperData->generateUrlKey(
                    $postModel->getResource(),
                    $postModel,
                    $post['identifier']
                ),
                'image'             => $post['featured_img'],
                'created_at'        => ($post['creation_time'] > $this->date->date()
                    || !$post['creation_time']) ? $this->date->date() : $post['creation_time'],
                'updated_at'        => ($post['update_time']) ?: $this->date->date(),
                'publish_date'      => ($post['publish_time']) ?: $this->date->date(),
                'enabled'           => (int) $post['is_active'],
                'in_rss'            => 0,
                'allow_comment'     => 1,
                'store_ids'         => $this->_storeManager->getStore()->getId(),
                'meta_robots'       => 'INDEX,FOLLOW',
                'meta_keywords'     => $post['meta_keywords'],
                'meta_description'  => $post['meta_description'],
                'author_id'         => (int) $post['author_id'],
                'import_source'     => $importSource . '-' . $post['post_id']
            ];
        }

        /** update and replace behaviour action */
        if ($isReplace && isset($sourceItems)) {
            foreach ($sourceItems as $post) {
                /** check the posts is imported */
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
                    /** check the posts isn't imported
                     *  Update posts
                     */
                    if ($data['behaviour'] === 'update'
                        && $data['expand_behaviour'] == '1'
                        && $post['is_duplicated_url'] != null) {
                        $where = ['post_id = ?' => (int) $post['is_duplicated_url']];
                        $this->_updatePosts($post, $where);
                        $this->_successCount++;
                        $this->_hasData = true;
                    } else {
                        /** Add new posts */
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
            }
            /**
             * Store old post ids
             */
            foreach ($postModel->getCollection() as $item) {
                if ($item->getImportSource() != null) {
                    $postImportSource = explode('-', $item->getImportSource());
                    $importType       = array_shift($postImportSource);
                    if ($importType == $this->_type['magefan']) {
                        $oldPostId                  = array_pop($postImportSource);
                        $oldPostIds[$item->getId()] = $oldPostId;
                    }
                }
            }
            mysqli_free_result($result);

            /**
             * Insert topics
             */
            $topicSql    = "SELECT post_id FROM `" . $data['table_prefix'] . self::TABLE_POST_RELATED
                . "` GROUP BY post_id";
            $topicCount  = 1;
            $oldTopicIds = [];
            $result      = mysqli_query($connection, $topicSql);

            while ($topic = mysqli_fetch_assoc($result)) {
                if ($topicModel->getResource()->isImported($importSource, $topic['post_id']) == false) {
                    try {
                        $topicModel->setData([
                            'name'          => 'magefan-topic-' . $topicCount,
                            'enabled'       => 1,
                            'store_ids'     => $this->_storeManager->getStore()->getId(),
                            'import_source' => $importSource . '-' . $topic['post_id']
                        ])->save();
                        $oldTopicIds[$topicModel->getId()] = $topic['post_id'];
                    } catch (Exception $e) {
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
            $topicPostSql   = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_POST_RELATED . "`";
            $result         = mysqli_query($connection, $topicPostSql);

            while ($topicPost = mysqli_fetch_assoc($result)) {
                $newPostId  = array_search($topicPost['related_id'], $oldPostIds);
                $newTopicId = array_search($topicPost['post_id'], $oldTopicIds);
                try {
                    $this->_resourceConnection->getConnection()->insert($topicPostTable, [
                        'topic_id' => $newTopicId,
                        'post_id'  => $newPostId,
                        'position' => 0
                    ]);
                } catch (Exception $e) {
                    continue;
                }
            }
            mysqli_free_result($result);
        }

        $statistics = $this->_getStatistics('posts', $this->_successCount, $this->_errorCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_post_ids_collection', $oldPostIds);
        $this->_registry->register('mageplaza_import_post_statistic', $statistics);

        return true;
    }

    /**
     * @param array $data
     * @param null $connection
     *
     * @return mixed|void
     * @throws LocalizedException
     */
    protected function _importTags($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_TAG . "`";
        $result    = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $isReplace = true;
        $oldTagIds = [];

        /** @var Tag $tagModel */
        $tagModel     = $this->_tagFactory->create();
        $importSource = $data['type'] . '-' . $data['database'];

        /** delete behaviour action */
        if ($data['behaviour'] === 'delete' || $data['behaviour'] === 'replace') {
            $tagModel->getResource()->deleteImportItems($data['type']);
            $this->_hasData = true;
            $isReplace      = !($data['behaviour'] === 'delete');
        }

        /** fetch all items from import source */
        while ($tag = mysqli_fetch_assoc($result)) {
            /** store the source item */
            $sourceItems[] = [
                'is_imported'       => $tagModel->getResource()->isImported($importSource, $tag['tag_id']),
                'is_duplicated_url' => $tagModel->getResource()->isDuplicateUrlKey($tag['identifier']),
                'id'                => $tag['tag_id'],
                'name'              => $tag['title'],
                'url_key'           => $this->helperData->generateUrlKey(
                    $tagModel->getResource(),
                    $tagModel,
                    $tag['identifier']
                ),
                'meta_robots'       => 'INDEX,FOLLOW',
                'meta_description'  => $tag['meta_description'],
                'meta_keywords'     => $tag['meta_keywords'],
                'meta_title'        => $tag['meta_title'],
                'store_ids'         => $this->_storeManager->getStore()->getId(),
                'enabled'           => (int) $tag['is_active'],
                'import_source'     => $importSource . '-' . $tag['tag_id']
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
                    /**
                     * check the posts isn't imported
                     * Update tags
                     */
                    if ($data['behaviour'] === 'update'
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
                    if ($importType == $this->_type['magefan']) {
                        $oldTagId                  = array_pop($tagImportSource);
                        $oldTagIds[$item->getId()] = $oldTagId;
                    }
                }
            }

            /** Insert post tag relation */
            $tagPostTable = $this->_resourceConnection->getTableName('mageplaza_blog_post_tag');
            $sqlTagPost   = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_POST_TAG . "` ";
            $result       = mysqli_query($connection, $sqlTagPost);
            $oldPostIds   = $this->_registry->registry('mageplaza_import_post_ids_collection');
            while ($tagPost = mysqli_fetch_assoc($result)) {
                $newPostId = array_search($tagPost['post_id'], $oldPostIds);
                $newTagId  = array_search($tagPost['tag_id'], $oldTagIds);
                try {
                    $this->_resourceConnection->getConnection()->insert($tagPostTable, [
                        'tag_id'   => $newTagId,
                        'post_id'  => $newPostId,
                        'position' => 0
                    ]);
                } catch (Exception $e) {
                    continue;
                }
            }
            mysqli_free_result($result);
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
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_CATEGORY . "`";
        $result    = mysqli_query($connection, $sqlString);
        $isReplace = true;
        /**
         * @var CategoryFactory
         */
        $categoryModel  = $this->_categoryFactory->create();
        $oldCategoryIds = [];
        $newCategories  = [];
        $oldCategories  = [];
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
                'is_imported'       => $categoryModel->getResource()->isImported(
                    $importSource,
                    $category['category_id']
                ),
                'is_duplicated_url' => $categoryModel->getResource()->isDuplicateUrlKey($category['identifier']),
                'id'                => $category['category_id'],
                'name'              => $category['title'],
                'url_key'           => $this->helperData->generateUrlKey(
                    $categoryModel->getResource(),
                    $categoryModel,
                    $category['identifier']
                ),
                'path'              => '1',
                'meta_robots'       => 'INDEX,FOLLOW',
                'store_ids'         => $this->_storeManager->getStore()->getId(),
                'enabled'           => (int) $category['is_active'],
                'meta_description'  => $category['meta_description'],
                'meta_keywords'     => $category['meta_keywords'],
                'meta_title'        => $category['meta_title'],
                'import_source'     => $importSource . '-' . $category['category_id']
            ];
        }

        /** update and replace behaviour action */
        if ($isReplace && isset($sourceItems)) {
            foreach ($sourceItems as $category) {
                if ($category['is_imported']) {
                    /** update category that has duplicate URK key */
                    if (($category['is_duplicated_url'] != null || $data['expand_behaviour'] == '1')
                        && $category['url_key'] !== 'root') {
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
                    if ($data['behaviour'] === 'update'
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
                            $newCategories[$categoryModel->getId()] = $category;
                            $this->_successCount++;
                            $this->_hasData = true;
                        } catch (Exception $e) {
                            $this->_errorCount++;
                            $this->_hasData = true;
                            continue;
                        }
                    }
                }
                $oldCategories[$category['id']] = $category;
            }

            /**
             * Store old category ids
             */
            foreach ($categoryModel->getCollection() as $item) {
                if ($item->getImportSource() != null) {
                    $catImportSource = explode('-', $item->getImportSource());
                    $importType      = array_shift($catImportSource);
                    if ($importType == $this->_type['magefan']) {
                        $oldCategoryId                  = array_pop($catImportSource);
                        $oldCategoryIds[$item->getId()] = $oldCategoryId;
                    }
                }
            }

            /** Import parent-child category */
            foreach ($newCategories as $newCatId => $newCategory) {
                if ($newCategory['path'] != '0' && $newCategory['path'] != null) {
                    $oldParentId     = explode('/', $newCategory['path']);
                    $oldParentId     = array_pop($oldParentId);
                    $parentId        = array_search($oldParentId, $oldCategoryIds);
                    $parentPath      = $categoryModel->load($parentId)->getPath();
                    $parentPaths     = explode('/', $categoryModel->getPath());
                    $level           = count($parentPaths);
                    $newPath         = $parentPath . '/' . $newCatId;
                    $currentCategory = $categoryModel->load($newCatId);
                    $currentCategory->setPath($newPath)->setParentId($parentId)->setLevel($level)->save();
                }
            }
            mysqli_free_result($result);

            /**
             * Import category post relation
             */
            $categoryPostTable = $this->_resourceConnection->getTableName('mageplaza_blog_post_category');
            $sqlCategoryPost   = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_POST_CATEGORY . "` ";
            $result            = mysqli_query($connection, $sqlCategoryPost);
            $oldPostIds        = $this->_registry->registry('mageplaza_import_post_ids_collection');
            while ($categoryPost = mysqli_fetch_assoc($result)) {
                $newPostId = array_search($categoryPost['post_id'], $oldPostIds);
                $newTagId  = array_search($categoryPost['category_id'], $oldCategoryIds);
                try {
                    $this->_resourceConnection->getConnection()->insert($categoryPostTable, [
                        'category_id' => $newTagId,
                        'post_id'     => $newPostId,
                        'position'    => 0
                    ]);
                } catch (Exception $e) {
                    continue;
                }
            }
        }
        $statistics = $this->_getStatistics('categories', $this->_successCount, $this->_errorCount, $this->_hasData);
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
        $sqlString     = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_COMMENT . "`
                      LEFT JOIN `" . $data['table_prefix'] . self::TABLE_CUSTOMER . "`
                      ON `" . $data['table_prefix'] . self::TABLE_COMMENT . "`.`customer_id` = `"
            . $data['table_prefix'] . self::TABLE_CUSTOMER . "`.`entity_id`";
        $result        = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $isReplace = true;
        /** @var Comment $commentModel */
        $commentModel  = $this->_commentFactory->create();
        $oldCommentIds = [];
        $newComments   = [];
        $customerModel = $this->_customerFactory->create();
        $websiteId     = $this->_storeManager->getWebsite()->getId();
        $oldPostIds    = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $importSource  = $data['type'] . '-' . $data['database'];

        /** delete behaviour action */
        if ($data['behaviour'] === 'delete' || $data['behaviour'] === 'replace') {
            $commentModel->getResource()->deleteImportItems($data['type']);
            $this->_hasData = true;
            if ($data['behaviour'] === 'delete') {
                $isReplace = false;
            } else {
                $isReplace = true;
            }
        }

        /** fetch all items from import source */
        while ($comment = mysqli_fetch_assoc($result)) {
            /**
             * mapping status
             */
            switch ($comment['status']) {
                case '2':
                    $status = Status::SPAM;
                    break;
                case '1':
                    $status = Status::APPROVED;
                    break;
                case '0':
                    $status = Status::PENDING;
                    break;
                default:
                    $status = Status::APPROVED;
            }
            /** search for new post id */
            $newPostId    = array_search($comment['post_id'], $oldPostIds);
            $commentEmail = ($comment['customer_id'] == 0) ? $comment['author_email'] : $comment['email'];
            /** check if comment author is customer */
            if ($accountManage->isEmailAvailable($commentEmail, $websiteId)) {
                $entityId  = 0;
                $userName  = $comment['author_nickname'];
                $userEmail = $commentEmail;
            } else {
                /** comment author is guest */
                $customerModel->setWebsiteId($websiteId);
                $customerModel->loadByEmail($commentEmail);
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
                'content'       => $comment['text'],
                'created_at'    => ($comment['creation_time']) ?: $this->date->date(),
                'status'        => $status,
                'store_ids'     => $this->_storeManager->getStore()->getId(),
                'user_name'     => $userName,
                'user_email'    => $userEmail,
                'parent_id'     => $comment['parent_id'],
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
                        $newComments[$commentModel->getId()] = $comment;
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

            /** Store old comment ids */
            foreach ($commentModel->getCollection() as $item) {
                if ($item->getImportSource() != null) {
                    $commentImportSource = explode('-', $item->getImportSource());
                    $importType          = array_shift($commentImportSource);
                    if ($importType == $this->_type['magefan']) {
                        $oldCommentId                  = array_pop($commentImportSource);
                        $oldCommentIds[$item->getId()] = $oldCommentId;
                    }
                }
            }

            /** Insert child-parent comments */
            foreach ($newComments as $newCommentId => $newComment) {
                if ($newComment['parent_id'] != '0') {
                    $oldParentId          = $newComment['parent_id'];
                    $parentId             = array_search($oldParentId, $oldCommentIds);
                    $currentParentComment = $commentModel->load($parentId);
                    if ($currentParentComment->getHasReply() == '0') {
                        $currentParentComment->setHasReply('1')->save();
                    }
                    $currentComment = $commentModel->load($newCommentId);
                    $currentComment->setIsReply('1')->setReplyId($parentId)->save();
                }
            }
        }

        $statistics = $this->_getStatistics('comments', $this->_successCount, $this->_errorCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_comment_statistic', $statistics);
    }

    /**
     * Import Author
     *
     * @param array $data
     * @param null $connection
     *
     * @return mixed|void
     */
    protected function _importAuthors($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_ADMIN_USER . "`";
        $result    = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $oldUserIds       = [];
        $magentoUserEmail = [];

        /** @var CustomerFactory */
        $customerModel = $this->_customerFactory->create();

        foreach ($customerModel->getCollection() as $customer) {
            $magentoUserEmail [$customer->getEmail()] = $customer->getId();
        }
        while ($user = mysqli_fetch_assoc($result)) {
            /**
             * @var Author
             */
            $userModel = $this->authorFactory->create();
            if (array_key_exists($user['email'], $magentoUserEmail)) {
                $customerId = $magentoUserEmail[$user['email']];
                $userModel->load($customerId, 'customer_id');
            } else {
                $customerId = 0;
                $userModel->load($user['email'], 'email');
            }

            if (!$userModel->getId()) {
                try {
                    $userModel->setData([
                        'name'        => $user['firstname'],
                        'url_key'     => $user['firstname'],
                        'email'       => $user['email'],
                        'customer_id' => $customerId,
                        'type'        => $userModel->getId() ? AuthorType::CUSTOMER : AuthorType::ADMIN
                    ])->save();
                    $this->_successCount++;
                    $this->_hasData = true;
                } catch (Exception $e) {
                    $this->_errorCount++;
                    $this->_hasData = true;
                    continue;
                }
            }

            $oldUserIds[$userModel->getId()] = $user['user_id'];
        }
        mysqli_free_result($result);

        /**
         * Import post author relation
         */
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $updateData = [];
        foreach ($oldUserIds as $newUserId => $oldUserId) {
            $relationshipSql = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_POST . "` WHERE author_id = "
                . $oldUserId;
            $result          = mysqli_query($connection, $relationshipSql);
            while ($postAuthor = mysqli_fetch_assoc($result)) {
                $newPostId              = array_search($postAuthor['post_id'], $oldPostIds);
                $updateData[$newPostId] = $newUserId;
            }
            mysqli_free_result($result);
        }
        foreach ($updateData as $postId => $authorId) {
            $where = ['post_id = ?' => (int) $postId];
            $this->_resourceConnection->getConnection()
                ->update(
                    $this->_resourceConnection->getTableName('mageplaza_blog_post'),
                    ['author_id' => $authorId],
                    $where
                );
        }
        $statistics = $this->_getStatistics('authors', $this->_successCount, $this->_errorCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_user_statistic', $statistics);
    }

    /**
     * @param Post $postModel
     * @param array $post
     *
     * @throws Exception
     */
    private function _addPosts($postModel, $post)
    {
        $postModel->setData([
            'name'              => $post['name'],
            'short_description' => $post['short_description'],
            'post_content'      => $post['post_content'],
            'url_key'           => $post['url_key'],
            'image'             => $post['image'],
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
     * @param array $post
     * @param array $where
     */
    private function _updatePosts($post, $where)
    {
        $this->_resourceConnection->getConnection()
            ->update(
                $this->_resourceConnection->getTableName('mageplaza_blog_post'),
                [
                    'name'              => $post['name'],
                    'short_description' => $post['short_description'],
                    'post_content'      => $post['post_content'],
                    'url_key'           => $post['url_key'],
                    'image'             => $post['image'],
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
                    'import_source'     => $post['import_source']
                ],
                $where
            );
        $this->_resourceConnection->getConnection()->delete($this->_resourceConnection
            ->getTableName('mageplaza_blog_post_category'), $where);
        $this->_resourceConnection->getConnection()->delete($this->_resourceConnection
            ->getTableName('mageplaza_blog_post_tag'), $where);
    }

    /**
     * @param Tag $tagModel
     * @param array $tag
     *
     * @throws Exception
     */
    private function _addTags($tagModel, $tag)
    {
        $tagModel->setData([
            'name'             => $tag['name'],
            'url_key'          => $tag['url_key'],
            'meta_robots'      => $tag['meta_robots'],
            'meta_description' => $tag['meta_description'],
            'meta_keywords'    => $tag['meta_keywords'],
            'meta_title'       => $tag['meta_title'],
            'store_ids'        => $tag['store_ids'],
            'enabled'          => $tag['enabled'],
            'import_source'    => $tag['import_source']
        ])->save();
    }

    /**
     * @param array $tag
     * @param array $where
     */
    private function _updateTags($tag, $where)
    {
        $this->_resourceConnection->getConnection()
            ->update(
                $this->_resourceConnection->getTableName('mageplaza_blog_tag'),
                [
                    'name'             => $tag['name'],
                    'url_key'          => $tag['url_key'],
                    'meta_robots'      => $tag['meta_robots'],
                    'meta_description' => $tag['meta_description'],
                    'meta_keywords'    => $tag['meta_keywords'],
                    'meta_title'       => $tag['meta_title'],
                    'store_ids'        => $tag['store_ids'],
                    'enabled'          => $tag['enabled'],
                    'import_source'    => $tag['import_source']
                ],
                $where
            );
    }

    /**
     * @param Category $categoryModel
     * @param array $category
     *
     * @throws Exception
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
            'meta_title'       => $category['meta_title'],
            'import_source'    => $category['import_source']
        ])->save();
    }

    /**
     * @param array $category
     * @param array $where
     */
    private function _updateCategories($category, $where)
    {
        $this->_resourceConnection->getConnection()
            ->update(
                $this->_resourceConnection->getTableName('mageplaza_blog_category'),
                [
                    'name'             => $category['name'],
                    'url_key'          => $category['url_key'],
                    'meta_robots'      => $category['meta_robots'],
                    'store_ids'        => $category['store_ids'],
                    'enabled'          => $category['enabled'],
                    'meta_description' => $category['meta_description'],
                    'meta_keywords'    => $category['meta_keywords'],
                    'meta_title'       => $category['meta_title'],
                    'import_source'    => $category['import_source']
                ],
                $where
            );
    }
}
