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
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Model\Author;
use Mageplaza\Blog\Model\Category;
use Mageplaza\Blog\Model\Comment;
use Mageplaza\Blog\Model\Config\Source\AuthorType;
use Mageplaza\Blog\Model\Config\Source\Comments\Status;
use Mageplaza\Blog\Model\Post;
use Mageplaza\Blog\Model\Tag;

/**
 * Class WordPress
 * @package Mageplaza\Blog\Model\Import
 */
class WordPress extends AbstractImport
{
    /**
     * Wordpress Post table name
     *
     * @var string
     */
    const TABLE_POST = 'posts';

    /**
     * Wordpress Post meta table name
     *
     * @var string
     */
    const TABLE_POSTMETA = 'postmeta';

    /**
     * Wordpress Category/Tag table name
     *
     * @var string
     */
    const TABLE_TERMS = 'terms';

    /**
     * Wordpress Category/Tag identify table name
     *
     * @var string
     */
    const TABLE_TERMTAXONOMY = 'term_taxonomy';

    /**
     * Wordpress Category/Tag relationship table name
     *
     * @var string
     */
    const TABLE_TERMRELATIONSHIP = 'term_relationships';

    /**
     * Magento User table name
     *
     * @var string
     */
    const TABLE_USERS = 'users';

    /**
     * Wordpress Comment table name
     *
     * @var string
     */
    const TABLE_COMMENT = 'comments';

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

        if ($this->_importPosts($data, $connection) && $data['type'] == $this->_type['wordpress']) {
            $this->_importTags($data, $connection);
            $this->_importCategories($data, $connection);
            $this->_importAuthors($data, $connection);
            $this->_importComments($data, $connection);

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
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_POST . "` "
            . "WHERE post_type = 'post' AND post_status <> 'auto-draft'";
        $result    = mysqli_query($connection, $sqlString);
        $isReplace = true;
        if (!$result) {
            return false;
        }

        $this->_resetRecords();
        /** @var Post $postModel */
        $postModel    = $this->_postFactory->create();
        $oldPostIds   = [];
        $importSource = $data['type'] . '-' . $data['database'];

        /** delete behaviour action */
        if ($data['behaviour'] === 'delete' || $data['behaviour'] === 'replace') {
            $this->_successCount = $postModel->getResource()->deleteImportItems($data['type']);
            $this->_hasData      = true;
            if ($data['behaviour'] === 'delete') {
                $isReplace = false;
            } else {
                $isReplace = true;
            }
        }

        /** fetch all items from import source */
        while ($post = mysqli_fetch_assoc($result)) {
            $content = $post['post_content'];
            $content = preg_replace(
                "/(http:\/\/)(.+?)(\/wp-content\/)/",
                $this->_helperImage->getBaseMediaUrl() . "/wysiwyg/",
                $content
            );
            /** store the source item */
            $sourceItems[] = [
                'is_imported'       => $postModel->getResource()->isImported($importSource, $post['ID']),
                'is_duplicated_url' => $postModel->getResource()->isDuplicateUrlKey($post['post_name']),
                'id'                => $post['ID'],
                'name'              => $post['post_title'],
                'short_description' => '',
                'post_content'      => $content,
                'url_key'           => $this->helperData->generateUrlKey(
                    $postModel->getResource(),
                    $postModel,
                    $post['post_name']
                ),
                'created_at'        => ($post['post_date_gmt'] > $this->date->date()
                    || !$post['post_date_gmt']) ? $this->date->date() : ($post['post_date_gmt']),
                'updated_at'        => ($post['post_modified_gmt']) ?: $this->date->date(),
                'publish_date'      => ($post['post_date_gmt']) ?: $this->date->date(),
                'enabled'           => ($post['post_status'] === 'trash') ? 0 : 1,
                'in_rss'            => 0,
                'allow_comment'     => 1,
                'store_ids'         => $this->_storeManager->getStore()->getId(),
                'meta_robots'       => 'INDEX,FOLLOW',
                'import_source'     => $importSource . '-' . $post['ID']
            ];
        }

        /** update and replace behaviour action */
        if ($isReplace && isset($sourceItems)) {
            foreach ($sourceItems as $post) {
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
                     * Update posts
                     */
                    if ($data['behaviour'] === 'update'
                        && $data['expand_behaviour'] == '1'
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
            }
            foreach ($postModel->getCollection() as $item) {
                if ($item->getImportSource() != null) {
                    $postImportSource = explode('-', $item->getImportSource());
                    $importType       = array_shift($postImportSource);

                    if ($importType == $this->_type['wordpress']) {
                        $oldPostId                  = array_pop($postImportSource);
                        $oldPostIds[$item->getId()] = $oldPostId;
                    }
                }
            }
            /** Import post image banner */
            $oldPostMetaIds = [];
            $updateData     = [];
            foreach ($oldPostIds as $newPostId => $oldPostId) {
                $postMetaSqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_POST
                    . "` WHERE `post_type` = 'attachment' and `post_parent` = '" . $oldPostId . "'";

                $result = mysqli_query($connection, $postMetaSqlString);
                if ($result) {
                    while ($postMeta = mysqli_fetch_assoc($result)) {
                        $oldPostMetaIds [$newPostId] = $postMeta['ID'];
                    }
                }
            }
            foreach ($oldPostMetaIds as $newPostId => $oldPostMetaId) {
                $postMetaSqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_POSTMETA
                    . "` WHERE `meta_key` = '_wp_attached_file' and `post_id` = '" . $oldPostMetaId . "'";
                $result            = mysqli_query($connection, $postMetaSqlString);
                if ($result) {
                    while ($postMeta = mysqli_fetch_assoc($result)) {
                        $updateData [$newPostId] = 'uploads/' . $postMeta['meta_value'];
                    }
                }
            }
            foreach ($updateData as $postId => $postImage) {
                $where = ['post_id = ?' => (int) $postId];
                $this->_resourceConnection->getConnection()
                    ->update(
                        $this->_resourceConnection->getTableName('mageplaza_blog_post'),
                        ['image' => $postImage],
                        $where
                    );
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
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_TERMS
            . "` INNER JOIN `" . $data['table_prefix'] . self::TABLE_TERMTAXONOMY . "` ON "
            . $data['table_prefix'] . self::TABLE_TERMS . ".term_id=" . $data['table_prefix']
            . self::TABLE_TERMTAXONOMY . ".term_id  WHERE "
            . $data['table_prefix'] . self::TABLE_TERMTAXONOMY . ".taxonomy = 'post_tag'";
        $result    = mysqli_query($connection, $sqlString);
        $oldTagIds = [];
        $this->_resetRecords();
        $isReplace = true;
        /** @var Tag $tagModel */
        $tagModel     = $this->_tagFactory->create();
        $importSource = $data['type'] . '-' . $data['database'];

        /** delete behaviour action */
        if ($data['behaviour'] === 'delete' || $data['behaviour'] === 'replace') {
            $tagModel->getResource()->deleteImportItems($data['type']);
            $this->_hasData = true;
            $isReplace      = ($data['behaviour'] !== 'delete');
        }

        /** fetch all items from import source */
        while ($tag = mysqli_fetch_assoc($result)) {
            /** store the source item */
            $sourceItems[] = [
                'is_imported'       => $tagModel->getResource()->isImported($importSource, $tag['term_id']),
                'is_duplicated_url' => $tagModel->getResource()->isDuplicateUrlKey($tag['slug']),
                'id'                => $tag['term_id'],
                'name'              => $tag['name'],
                'url_key'           => $this->helperData->generateUrlKey(
                    $tagModel->getResource(),
                    $tagModel,
                    $tag['slug']
                ),
                'description'       => $tag['description'],
                'meta_robots'       => 'INDEX,FOLLOW',
                'store_ids'         => $this->_storeManager->getStore()->getId(),
                'enabled'           => 1,
                'import_source'     => $importSource . '-' . $tag['term_id']
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
                     * Update tags
                     */
                    if ($data['behaviour'] == 'update' && $data['expand_behaviour'] == '1'
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
                        /**
                         * Re-import the existing tags
                         */
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

            /**
             * Store old tag ids
             */
            foreach ($tagModel->getCollection() as $item) {
                if ($item->getImportSource() != null) {
                    $tagImportSource = explode('-', $item->getImportSource());
                    $importType      = array_shift($tagImportSource);
                    if ($importType == $this->_type['wordpress']) {
                        $oldTagId                  = array_pop($tagImportSource);
                        $oldTagIds[$item->getId()] = $oldTagId;
                    }
                }
            }
            $this->_importRelationships($data, $connection, $oldTagIds, 'mageplaza_blog_post_tag', 'post_tag');
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
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_TERMS
            . "` INNER JOIN `" . $data['table_prefix'] . self::TABLE_TERMTAXONOMY
            . "` ON " . $data['table_prefix'] . self::TABLE_TERMS . ".term_id=" . $data['table_prefix']
            . self::TABLE_TERMTAXONOMY . ".term_id WHERE " . $data['table_prefix']
            . self::TABLE_TERMTAXONOMY . ".taxonomy = 'category' AND " . $data['table_prefix']
            . self::TABLE_TERMS . ".name <> 'uncategorized' ";
        $result    = mysqli_query($connection, $sqlString);
        $isReplace = true;
        /** @var Category */
        $categoryModel  = $this->_categoryFactory->create();
        $newCategories  = [];
        $oldCategories  = [];
        $oldCategoryIds = [];
        $this->_resetRecords();
        $importSource = $data['type'] . '-' . $data['database'];

        /** delete behaviour action */
        if ($data['behaviour'] === 'delete' || $data['behaviour'] === 'replace') {
            $categoryModel->getResource()->deleteImportItems($data['type']);
            $this->_hasData = true;
            $isReplace      = ($data['behaviour'] !== 'delete');
        }

        /** fetch all items from import source */
        while ($category = mysqli_fetch_assoc($result)) {
            /** store the source item */
            $sourceItems[] = [
                'is_imported'       => $categoryModel->getResource()->isImported($importSource, $category['term_id']),
                'is_duplicated_url' => $categoryModel->getResource()->isDuplicateUrlKey($category['slug']),
                'id'                => $category['term_id'],
                'name'              => $category['name'],
                'url_key'           => $this->helperData->generateUrlKey(
                    $categoryModel->getResource(),
                    $categoryModel,
                    $category['slug']
                ),
                'description'       => $category['description'],
                'meta_robots'       => 'INDEX,FOLLOW',
                'store_ids'         => $this->_storeManager->getStore()->getId(),
                'enabled'           => 1,
                'path'              => '1',
                'parent'            => $category['parent'],
                'import_source'     => $importSource . '-' . $category['term_id']
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
                        && $category['url_key'] !== 'root') {
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
                         * Re-import the existing categories
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
                    if ($importType == $this->_type['wordpress']) {
                        $oldCategoryId                  = array_pop($catImportSource);
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
                        $parentId        = array_search($newCategory['parent'], $oldCategoryIds);
                        $parentPath      = $categoryModel->load($parentId)->getPath();
                        $parentPaths     = explode('/', $categoryModel->getPath());
                        $level           = count($parentPaths);
                        $newPath         = $parentPath . '/' . $newCatId;
                        $currentCategory = $categoryModel->load($newCatId);
                        $currentCategory->setPath($newPath)->setParentId($parentId)->setLevel($level)->save();
                    }
                }
            }
            mysqli_free_result($result);
            $this->_importRelationships(
                $data,
                $connection,
                $oldCategoryIds,
                'mageplaza_blog_post_category',
                'category',
                1
            );
        }

        $statistics = $this->_getStatistics('categories', $this->_successCount, $this->_errorCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_category_statistic', $statistics);
    }

    /**
     * Import authors
     *
     * @param array $data
     * @param null $connection
     *
     * @return mixed|void
     */
    protected function _importAuthors($data, $connection)
    {
        $sqlString = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_USERS . "`";
        $result    = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $oldUserIds       = [];
        $magentoUserEmail = [];

        /**
         * @var CustomerFactory
         */
        $customerModel = $this->_customerFactory->create();

        foreach ($customerModel->getCollection() as $customer) {
            $magentoUserEmail[$customer->getEmail()] = $customer->getId();
        }
        while ($user = mysqli_fetch_assoc($result)) {
            /** @var Author $userModel */
            $userModel = $this->authorFactory->create();
            if (array_key_exists($user['user_email'], $magentoUserEmail)) {
                $customerId = $magentoUserEmail[$user['user_email']];
                $userModel->load($customerId, 'customer_id');
            } else {
                $customerId = 0;
                $userModel->load($user['user_email'], 'email');
            }

            if (!$userModel->getId()) {
                try {
                    $userModel->setData([
                        'name'        => $user['user_login'],
                        'url_key'     => $user['user_login'],
                        'email'       => $user['user_email'],
                        'customer_id' => $customerId,
                        'type'        => AuthorType::ADMIN
                    ])->save();
                    $this->_successCount++;
                    $this->_hasData = true;
                } catch (Exception $e) {
                    $this->_errorCount++;
                    $this->_hasData = true;
                    continue;
                }
            }

            $oldUserIds[$userModel->getId()] = $user['ID'];

        }

        mysqli_free_result($result);

        /**
         * Import post author relation
         */
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $updateData = [];
        foreach ($oldUserIds as $newUserId => $oldUserId) {
            $relationshipSql = "SELECT ID FROM `" . $data['table_prefix'] . self::TABLE_POST
                . "` WHERE post_author = " . $oldUserId . " AND post_type = 'post' AND post_status <> 'auto-draft'";
            $result          = mysqli_query($connection, $relationshipSql);
            while ($postAuthor = mysqli_fetch_assoc($result)) {
                $newPostId              = array_search($postAuthor['ID'], $oldPostIds);
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
        $sqlString     = "SELECT * FROM `" . $data['table_prefix'] . self::TABLE_COMMENT . "`";
        $result        = mysqli_query($connection, $sqlString);
        $this->_resetRecords();
        $isReplace = true;
        /** @var Comment $commentModel */
        $commentModel  = $this->_commentFactory->create();
        $customerModel = $this->_customerFactory->create();
        $websiteId     = $this->_storeManager->getWebsite()->getId();
        $oldPostIds    = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $oldCommentIds = [];
        $importSource  = $data['type'] . '-' . $data['database'];

        /** delete behaviour action */
        if ($data['behaviour'] == 'delete' || $data['behaviour'] == 'replace') {
            $commentModel->getResource()->deleteImportItems($data['type']);
            $this->_hasData = true;
            if ($data['behaviour'] == 'delete') {
                $isReplace = false;
            } else {
                $isReplace = true;
            }
        }

        /** fetch all items from import source */
        while ($comment = mysqli_fetch_assoc($result)) {
            /** mapping status */
            switch ($comment['comment_approved']) {
                case '1':
                    $status = Status::APPROVED;
                    break;
                case '0':
                    $status = Status::PENDING;
                    break;
                case 'spam':
                    $status = Status::SPAM;
                    break;
                default:
                    $status = Status::SPAM;
            }
            $newPostId = array_search($comment['comment_post_ID'], $oldPostIds);
            if ($accountManage->isEmailAvailable($comment['comment_author_email'], $websiteId)) {
                $entityId  = 0;
                $userName  = $comment['comment_author'];
                $userEmail = $comment['comment_author_email'];
            } else {
                $customerModel->setWebsiteId($websiteId);
                $customerModel->loadByEmail($comment['comment_author_email']);
                $entityId  = $customerModel->getEntityId();
                $userName  = '';
                $userEmail = '';
            }

            /** store the source item */
            $sourceItems[] = [
                'is_imported'   => $commentModel->getResource()->isImported($importSource, $comment['comment_ID']),
                'id'            => $comment['comment_ID'],
                'post_id'       => $newPostId,
                'entity_id'     => $entityId,
                'has_reply'     => 0,
                'is_reply'      => 0,
                'reply_id'      => 0,
                'content'       => $comment['comment_content'],
                'created_at'    => ($comment['comment_date_gmt']) ?: $this->date->date(),
                'status'        => $status,
                'store_ids'     => $this->_storeManager->getStore()->getId(),
                'user_name'     => $userName,
                'user_email'    => $userEmail,
                'import_source' => $importSource . '-' . $comment['comment_ID']
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
                        $this->_hasData                         = true;
                        $oldCommentIds [$commentModel->getId()] = $comment['id'];
                    } catch (Exception $e) {
                        $this->_errorCount++;
                        $this->_hasData = true;
                        continue;
                    }
                }
            }

            $upgradeParentData = [];
            $upgradeChildData  = [];

            /**
             * Insert child-parent comments
             */
            foreach ($oldCommentIds as $newCommentId => $oldCommentId) {
                $relationshipSql = "SELECT `comment_ID`,`comment_parent` FROM `" . $data['table_prefix']
                    . self::TABLE_COMMENT . "` WHERE `comment_parent` <> 0";
                $result          = mysqli_query($connection, $relationshipSql);

                while ($commentParent = mysqli_fetch_assoc($result)) {
                    $newCommentParentId                     = array_search(
                        $commentParent['comment_parent'],
                        $oldCommentIds
                    );
                    $newCommentChildId                      = array_search(
                        $commentParent['comment_ID'],
                        $oldCommentIds
                    );
                    $upgradeChildData[$newCommentChildId]   = $newCommentParentId;
                    $upgradeParentData[$newCommentParentId] = 1;
                }
            }
            foreach ($upgradeChildData as $commentId => $commentParentId) {
                $where = ['comment_id = ?' => (int) $commentId];
                $this->_resourceConnection->getConnection()
                    ->update($this->_resourceConnection
                        ->getTableName('mageplaza_blog_comment'), ['reply_id' => $commentParentId], $where);
            }
            foreach ($upgradeParentData as $commentId => $commentHasReply) {
                $where = ['comment_id = ?' => (int) $commentId];
                $this->_resourceConnection->getConnection()
                    ->update($this->_resourceConnection
                        ->getTableName('mageplaza_blog_comment'), ['has_reply' => $commentHasReply], $where);
            }
            mysqli_free_result($result);
        }

        $statistics = $this->_getStatistics('comments', $this->_successCount, $this->_errorCount, $this->_hasData);
        $this->_registry->register('mageplaza_import_comment_statistic', $statistics);
    }

    /**
     * @param array $data
     * @param null $connection
     * @param array $oldTermIds
     * @param string $relationTable
     * @param string $termType
     * @param null $isCategory
     */
    protected function _importRelationships(
        $data,
        $connection,
        $oldTermIds,
        $relationTable,
        $termType,
        $isCategory = null
    ) {
        $oldPostIds        = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $categoryPostTable = $this->_resourceConnection->getTableName($relationTable);
        foreach ($oldPostIds as $newPostId => $oldPostId) {
            $sqlRelation = "SELECT `" . $data['table_prefix'] . self::TABLE_TERMTAXONOMY . "`.`term_id`
                      FROM `" . $data['table_prefix'] . self::TABLE_TERMTAXONOMY . "`
                      INNER JOIN `" . $data['table_prefix'] . self::TABLE_TERMRELATIONSHIP . "`
                      ON " . $data['table_prefix'] . self::TABLE_TERMTAXONOMY . ".`term_taxonomy_id`="
                . $data['table_prefix'] . self::TABLE_TERMRELATIONSHIP . ".`term_taxonomy_id`
                      RIGHT JOIN `" . $data['table_prefix'] . self::TABLE_TERMS . "`
                      ON " . $data['table_prefix'] . self::TABLE_TERMTAXONOMY . ".`term_id` = " . $data['table_prefix']
                . self::TABLE_TERMS . ".`term_id`
                      WHERE " . $data['table_prefix'] . self::TABLE_TERMTAXONOMY . ".taxonomy = '" . $termType . "'
                      AND `" . $data['table_prefix'] . self::TABLE_TERMRELATIONSHIP . "`.`object_id` = " . $oldPostId;
            $result      = mysqli_query($connection, $sqlRelation);
            while ($categoryPost = mysqli_fetch_assoc($result)) {
                if ($isCategory) {
                    $newCategoryId = (array_search($categoryPost['term_id'], $oldTermIds)) ?: '1';
                    $termId        = 'category_id';
                } else {
                    $newCategoryId = array_search($categoryPost['term_id'], $oldTermIds);
                    $termId        = 'tag_id';
                }
                try {
                    $this->_resourceConnection->getConnection()->insert($categoryPostTable, [
                        $termId    => $newCategoryId,
                        'post_id'  => $newPostId,
                        'position' => 0
                    ]);
                } catch (Exception $e) {
                    continue;
                }
            }
        }
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
            'created_at'        => $post['created_at'],
            'updated_at'        => $post['updated_at'],
            'publish_date'      => $post['publish_date'],
            'enabled'           => $post['enabled'],
            'in_rss'            => $post['in_rss'],
            'allow_comment'     => $post['allow_comment'],
            'store_ids'         => $post['store_ids'],
            'meta_robots'       => $post['meta_robots'],
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
                    'created_at'        => $post['created_at'],
                    'updated_at'        => $post['updated_at'],
                    'publish_date'      => $post['publish_date'],
                    'enabled'           => $post['enabled'],
                    'in_rss'            => $post['in_rss'],
                    'allow_comment'     => $post['allow_comment'],
                    'store_ids'         => $post['store_ids'],
                    'meta_robots'       => $post['meta_robots'],
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
     * @param Tag $tagModel
     * @param array $tag
     *
     * @throws Exception
     */
    private function _addTags($tagModel, $tag)
    {
        $tagModel->setData([
            'name'          => $tag['name'],
            'url_key'       => $tag['url_key'],
            'description'   => $tag['description'],
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
        $this->_resourceConnection->getConnection()
            ->update(
                $this->_resourceConnection->getTableName('mageplaza_blog_tag'),
                [
                    'name'          => $tag['name'],
                    'url_key'       => $tag['url_key'],
                    'description'   => $tag['description'],
                    'meta_robots'   => $tag['meta_robots'],
                    'store_ids'     => $tag['store_ids'],
                    'enabled'       => $tag['enabled'],
                    'import_source' => $tag['import_source']
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
            'name'          => $category['name'],
            'url_key'       => $category['url_key'],
            'description'   => $category['description'],
            'meta_robots'   => $category['meta_robots'],
            'store_ids'     => $category['store_ids'],
            'enabled'       => $category['enabled'],
            'path'          => $category['path'],
            'import_source' => $category['import_source']
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
                    'name'          => $category['name'],
                    'url_key'       => $category['url_key'],
                    'description'   => $category['description'],
                    'meta_robots'   => $category['meta_robots'],
                    'store_ids'     => $category['store_ids'],
                    'enabled'       => $category['enabled'],
                    'import_source' => $category['import_source']
                ],
                $where
            );
    }
}
