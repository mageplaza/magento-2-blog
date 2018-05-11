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

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection\AbstractDb;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\TagFactory;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\CommentFactory;
use Magento\User\Model\UserFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Mageplaza\Blog\Helper\Data as HelperData;
use Mageplaza\Blog\Helper\Image as HelperImage;

/**
 * Class WordPress
 * @package Mageplaza\Blog\Model
 */
class WordPress extends AbstractModel
{
    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var PostFactory
     */
    protected $_postFactory;

    /**
     * @var TagFactory
     */
    protected $_tagFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var CommentFactory
     */
    protected $_commentFactory;

    /**
     * @var UserFactory
     */
    protected $_userFactory;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var HelperData
     */
    protected $_helper;

    /**
     * @var HelperImage
     */
    protected $_helperImage;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * WordPress constructor.
     * @param Context $context
     * @param Registry $registry
     * @param PostFactory $postFactory
     * @param TagFactory $tagFactory
     * @param CategoryFactory $categoryFactory
     * @param CommentFactory $commentFactory
     * @param UserFactory $userFactory
     * @param CustomerFactory $customerFactory
     * @param ObjectManagerInterface $objectManager
     * @param ResourceConnection $resourceConnection
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
     * @param HelperData $helper
     * @param HelperImage $helperImage
     * @param AbstractDb|null $resourceCollection
     * @param AbstractResource|null $resource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PostFactory $postFactory,
        TagFactory $tagFactory,
        CategoryFactory $categoryFactory,
        CommentFactory $commentFactory,
        UserFactory $userFactory,
        CustomerFactory $customerFactory,
        ObjectManagerInterface $objectManager,
        ResourceConnection $resourceConnection,
        DateTime $date,
        StoreManagerInterface $storeManager,
        HelperData $helper,
        HelperImage $helperImage,
        AbstractDb $resourceCollection = null,
        AbstractResource $resource = null,
        array $data = []
    )
    {
        $this->date = $date;
        $this->_postFactory = $postFactory;
        $this->_tagFactory = $tagFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->_commentFactory = $commentFactory;
        $this->_userFactory = $userFactory;
        $this->_customerFactory = $customerFactory;
        $this->_objectManager = $objectManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
        $this->_helperImage = $helperImage;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param $data
     * @param $connection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function runImport($data, $connection)
    {
        mysqli_query($connection, 'SET NAMES "utf8"');
        $this->importPosts($data, $connection);
        $this->importTags($data, $connection);
        $this->importCategories($data, $connection);
        $this->importAuthors($data, $connection);
        $this->importComments($data, $connection);
    }

    /**
     * @param $data
     * @param $connection
     */
    public function importPosts($data, $connection)
    {
        $tablePrefix = $data["table_prefix"];
        $sqlString = "SELECT * FROM `" . $tablePrefix . "posts` WHERE post_type = 'post' AND post_status <> 'auto-draft'";
        $result = mysqli_query($connection, $sqlString);
        $errorCount = 0;
        $successCount = 0;
        $hasData = false;
        $postModel = $this->_postFactory->create();
        $deleteCount = $this->behaviour($postModel, $data);
        $oldPostIds = [];
        while ($post = mysqli_fetch_assoc($result)) {
            $createDate = (strtotime($post["post_date_gmt"]) > strtotime($this->date->date())) ? strtotime($this->date->date()) : strtotime($post["post_date_gmt"]);
            $modifyDate = strtotime($post["post_modified_gmt"]);
            $publicDate = strtotime($post["post_date_gmt"]);
            $content = $post["post_content"];
            $content = preg_replace("/(http:\/\/)(.+?)(\/wp-content\/)/", $this->_helperImage->getBaseMediaUrl() . "/wysiwyg/", $content);

            try {
                $postModel->setData([
                    "name" => $post["post_title"],
                    "short_description" => "",
                    "post_content" => $content,
                    "created_at" => $createDate,
                    "updated_at" => $modifyDate,
                    "publish_date" => $publicDate,
                    "enabled" => 1,
                    "in_rss" => 0,
                    "allow_comment" => 1,
                    "store_ids" => $this->_storeManager->getStore()->getId(),
                    "meta_robots" => "INDEX,FOLLOW",

                ])->save();
                $successCount++;
                $hasData = true;
                $oldPostIds [$postModel->getId()] = $post["ID"];

            } catch (\Exception $e) {
                $errorCount++;
                $hasData = true;
                continue;
            }
        }

        mysqli_free_result($result);
        $statistics = $this->getStatistics("posts", $successCount, $errorCount, $deleteCount, $hasData);
        $this->_registry->register('mageplaza_import_post_ids_collection', $oldPostIds);
        $this->_registry->register('mageplaza_import_post_statistic', $statistics);

    }

    /**
     * @param $data
     * @param $connection
     */
    public function importTags($data, $connection)
    {
        $tablePrefix = $data["table_prefix"];
        $sqlString = "SELECT * FROM `" . $tablePrefix . "terms` 
                          INNER JOIN `" . $tablePrefix . "term_taxonomy` 
                          ON " . $tablePrefix . "terms.term_id=" . $tablePrefix . "term_taxonomy.term_id 
                          WHERE " . $tablePrefix . "term_taxonomy.taxonomy = 'post_tag'";
        $result = mysqli_query($connection, $sqlString);
        $oldTagIds = [];
        $errorCount = 0;
        $successCount = 0;
        $hasData = false;
        $tagModel = $this->_tagFactory->create();
        $deleteCount = $this->behaviour($tagModel, $data);

        while ($tag = mysqli_fetch_assoc($result)) {
            try {
                $tagModel->setData([
                    "name" => $tag["name"],
                    "url_key" => $tag["slug"],
                    "description" => $tag["description"],
                    "meta_robots" => "INDEX,FOLLOW",
                    "store_ids" => $this->_storeManager->getStore()->getId(),
                    "enabled" => 1
                ])->save();
                $successCount++;
                $oldTagIds[$tagModel->getId()] = $tag["term_id"];
                $hasData = true;

            } catch (\Exception $e) {
                $errorCount++;
                $hasData = true;
                continue;
            }
        }
        mysqli_free_result($result);
        $this->importRelationships($data, $connection, $oldTagIds, 'mageplaza_blog_post_tag', 'post_tag');

        $statistics = $this->getStatistics("tags", $successCount, $errorCount, $deleteCount, $hasData);
        $this->_registry->register('mageplaza_import_tag_statistic', $statistics);

    }

    /**
     * @param $data
     * @param $connection
     */
    public function importCategories($data, $connection)
    {
        $tablePrefix = $data["table_prefix"];
        $sqlString = "SELECT * FROM `" . $tablePrefix . "terms` 
                          INNER JOIN `" . $tablePrefix . "term_taxonomy` 
                          ON " . $tablePrefix . "terms.term_id=" . $tablePrefix . "term_taxonomy.term_id 
                          WHERE " . $tablePrefix . "term_taxonomy.taxonomy = 'category' 
                          AND " . $tablePrefix . "terms.name <> 'uncategorized' ";
        $result = mysqli_query($connection, $sqlString);
        $categoryModel = $this->_categoryFactory->create();
        $newCategories = [];
        $oldCategories = [];
        $oldCategoryIds = [];
        $errorCount = 0;
        $successCount = 0;
        $hasData = false;
        $deleteCount = $this->behaviour($categoryModel, $data, 1);
        while ($category = mysqli_fetch_assoc($result)) {

            try {
                $categoryModel->setData([
                    "name" => $category["name"],
                    "url_key" => $category["slug"],
                    "description" => $category["description"],
                    "meta_robots" => "INDEX,FOLLOW",
                    "store_ids" => $this->_storeManager->getStore()->getId(),
                    "enabled" => 1,
                    "path" => '1'
                ])->save();

                $oldCategories[$category["term_id"]] = $category;
                $newCategories[$categoryModel->getId()] = $category;
                $oldCategoryIds[$categoryModel->getId()] = $category["term_id"];
                $successCount++;
                $hasData = true;

            } catch (\Exception $e) {
                $errorCount++;
                $hasData = true;
                continue;
            }

        }

        //import parent-child category
        foreach ($newCategories as $newCatId => $newCategory) {

            if ($newCategory["parent"] != "0") {
                if (isset($oldCategories[$newCategory["parent"]])) {

                    $parentId = array_search($newCategory["parent"], $oldCategoryIds);
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
        $this->importRelationships($data, $connection, $oldCategoryIds, 'mageplaza_blog_post_category', 'category', 1);

        $statistics = $this->getStatistics("categories", $successCount, $errorCount, $deleteCount, $hasData);
        $this->_registry->register('mageplaza_import_category_statistic', $statistics);
    }

    /**
     * @param $data
     * @param $connection
     */
    public function importAuthors($data, $connection)
    {
        $tablePrefix = $data["table_prefix"];
        $sqlString = "SELECT * FROM `" . $tablePrefix . "users`";
        $result = mysqli_query($connection, $sqlString);
        $errorCount = 0;
        $successCount = 0;
        $hasData = false;
        $oldUserIds = [];
        $magentoUserEmail = [];
        $userModel = $this->_userFactory->create();

        foreach ($userModel->getCollection() as $user) {
            $magentoUserEmail [] = $user->getEmail();
        }
        while ($user = mysqli_fetch_assoc($result)) {
            if (!in_array($user["user_email"], $magentoUserEmail)) {
                $createDate = strtotime($user["user_registered"]);
                try {
                    $userModel->setData([
                        "username" => $user["user_login"],
                        "firstname" => "WP-",
                        "lastname" => $user["display_name"],
                        "password" => $this->generatePassword(12),
                        "email" => $user["user_email"],
                        "is_active" => 1,
                        "interface_locale" => "en_US",
                        "created" => $createDate
                    ])->setRoleId(1)->save();
                    $successCount++;
                    $hasData = true;
                    $oldUserIds[$userModel->getId()] = $user["ID"];

                } catch (\Exception $e) {
                    $errorCount++;
                    $hasData = true;
                    continue;
                }
            } else {
                $oldUserIds[$user["ID"]] = $user["ID"];
            }
        }

        mysqli_free_result($result);

        //import post author relation
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $updateData = [];
        foreach ($oldUserIds as $newUserId => $oldUserId) {
            $relationshipSql = "SELECT ID FROM `" . $tablePrefix . "posts` 
                                  WHERE post_author = " . $oldUserId . " 
                                  AND post_type = 'post' 
                                  AND post_status <> 'auto-draft'";
            $result = mysqli_query($connection, $relationshipSql);
            while ($postAuthor = mysqli_fetch_assoc($result)) {
                $newPostId = array_search($postAuthor["ID"], $oldPostIds);
                $updateData[$newPostId] = $newUserId;
            }
        }
        foreach ($updateData as $postId => $authorId) {
            $where = ['post_id = ?' => (int)$postId];
            $this->_resourceConnection->update($this->_resourceConnection->getTableName('mageplaza_blog_post'), ['author_id' => $authorId], $where);
        }
        mysqli_free_result($result);
        $statistics = $this->getStatistics("authors", $successCount, $errorCount, 0, $hasData);
        $this->_registry->register('mageplaza_import_user_statistic', $statistics);
    }

    /**
     * @param $data
     * @param $connection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function importComments($data, $connection)
    {
        $tablePrefix = $data["table_prefix"];
        $accountManage = $this->_objectManager->create('\Magento\Customer\Model\AccountManagement');
        $sqlString = "SELECT * FROM `" . $tablePrefix . "comments`";
        $result = mysqli_query($connection, $sqlString);
        $errorCount = 0;
        $successCount = 0;
        $hasData = false;
        $commentModel = $this->_commentFactory->create();
        $deleteCount = $this->behaviour($commentModel, $data);
        $customerModel = $this->_customerFactory->create();
        $websiteId = $this->_storeManager->getWebsite()->getId();
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $oldCommentIds = [];

        while ($comment = mysqli_fetch_assoc($result)) {
            $createDate = strtotime($comment["comment_date_gmt"]);
            switch ($comment["comment_approved"]) {
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
            $newPostId = array_search($comment["comment_post_ID"], $oldPostIds);
            if ($accountManage->isEmailAvailable($comment["comment_author_email"], $websiteId)) {
                $entityId = 0;
                $userName = $comment["comment_author"];
                $userEmail = $comment["comment_author_email"];
            } else {
                $customerModel->setWebsiteId($websiteId);
                $customerModel->loadByEmail($comment["comment_author_email"]);
                $entityId = $customerModel->getEntityId();
                $userName = "";
                $userEmail = "";
            }

            try {
                $commentModel->setData([
                    "post_id" => $newPostId,
                    "entity_id" => $entityId,
                    "has_reply" => 0,
                    "is_reply" => 0,
                    "reply_id" => 0,
                    "content" => $comment["comment_content"],
                    "created_at" => $createDate,
                    "status" => $status,
                    "store_ids" => $this->_storeManager->getStore()->getId(),
                    "user_name" => $userName,
                    "user_email" => $userEmail
                ])->save();
                $successCount++;
                $hasData = true;
                $oldCommentIds [$commentModel->getId()] = $comment["comment_ID"];

            } catch (\Exception $e) {
                $errorCount++;
                $hasData = true;
                continue;
            }
        }
        mysqli_free_result($result);
        $upgradeParentData = [];
        $upgradeChildData = [];
        foreach ($oldCommentIds as $newCommentId => $oldCommentId) {
            $relationshipSql = "SELECT `comment_ID`,`comment_parent` FROM `" . $tablePrefix . "comments` WHERE `comment_parent` <> 0";
            $result = mysqli_query($connection, $relationshipSql);

            while ($commentParent = mysqli_fetch_assoc($result)) {
                $newCommentParentId = array_search($commentParent["comment_parent"], $oldCommentIds);
                $newCommentChildId = array_search($commentParent["comment_ID"], $oldCommentIds);
                $upgradeChildData[$newCommentChildId] = $newCommentParentId;
                $upgradeParentData[$newCommentParentId] = 1;
            }
        }
        foreach ($upgradeChildData as $commentId => $commentParentId) {
            $where = ['comment_id = ?' => (int)$commentId];
            $this->_resourceConnection->update($this->_resourceConnection->getTableName('mageplaza_blog_comment'), ['reply_id' => $commentParentId], $where);
        }
        foreach ($upgradeParentData as $commentId => $commentHasReply) {
            $where = ['comment_id = ?' => (int)$commentId];
            $this->_resourceConnection->update($this->_resourceConnection->getTableName('mageplaza_blog_comment'), ['has_reply' => $commentHasReply], $where);
        }
        mysqli_free_result($result);
        $statistics = $this->getStatistics("comments", $successCount, $errorCount, $deleteCount, $hasData);
        $this->_registry->register('mageplaza_import_comment_statistic', $statistics);
    }

    /**
     * @param $objectType
     * @param $data
     * @param null $isCategory
     * @return int
     */
    public function behaviour($objectType, $data, $isCategory = null)
    {
        $count = 0;
        if ($data["behaviour"] == "replace") {
            $collection = $objectType->getCollection();
            foreach ($collection as $item) {
                if ($isCategory) {
                    if ($item->getId() != "1") {
                        $item->delete();
                        $count++;
                    }
                } else {
                    $item->delete();
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * @param $data
     * @param $connection
     * @param $oldTermIds
     * @param $relationTable
     * @param $termType
     * @param null $isCategory
     */
    public function importRelationships($data, $connection, $oldTermIds, $relationTable, $termType, $isCategory = null)
    {
        $tablePrefix = $data["table_prefix"];
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $categoryPostTable = $this->_resourceConnection->getTableName($relationTable);

        foreach ($oldPostIds as $newPostId => $oldPostId) {

            $sqlRelation = "SELECT `" . $tablePrefix . "term_taxonomy`.`term_id` 
                          FROM `" . $tablePrefix . "term_taxonomy` 
                          INNER JOIN `" . $tablePrefix . "term_relationships` 
                          ON " . $tablePrefix . "term_taxonomy.`term_taxonomy_id`=" . $tablePrefix . "term_relationships.`term_taxonomy_id` 
                          RIGHT JOIN `" . $tablePrefix . "terms` 
                          ON " . $tablePrefix . "term_taxonomy.`term_id` = " . $tablePrefix . "terms.`term_id`
                          WHERE " . $tablePrefix . "term_taxonomy.taxonomy = '" . $termType . "' 
                          AND `" . $tablePrefix . "term_relationships`.`object_id` = " . $oldPostId;

            $result = mysqli_query($connection, $sqlRelation);
            $data = [];
            while ($categoryPost = mysqli_fetch_assoc($result)) {
                if ($isCategory) {
                    $newCategoryId = (array_search($categoryPost["term_id"], $oldTermIds)) ?: "1";
                    $termId = 'category_id';
                } else {
                    $newCategoryId = array_search($categoryPost["term_id"], $oldTermIds);
                    $termId = 'tag_id';
                }
                $data[] = [
                    $termId => $newCategoryId,
                    'post_id' => $newPostId,
                    'position' => 0
                ];
            }
        }
        $this->_resourceConnection->insertMultiple($categoryPostTable, $data);
    }

    /**
     * @param $type
     * @param $successCount
     * @param $errorCount
     * @param $deleteCount
     * @param $hasData
     * @return array
     */
    public function getStatistics($type, $successCount, $errorCount, $deleteCount, $hasData)
    {
        $statistics = [
            "type" => $type,
            "success_count" => $successCount,
            "error_count" => $errorCount,
            "delete_count" => $deleteCount,
            "has_data" => $hasData
        ];
        return $statistics;
    }

    /**
     * @param int $length
     * @param bool $add_dashes
     * @param string $available_sets
     * @return bool|string
     */
    function generatePassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = array();
        if (strpos($available_sets, 'l') !== false)
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        if (strpos($available_sets, 'u') !== false)
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        if (strpos($available_sets, 'd') !== false)
            $sets[] = '23456789';
        if (strpos($available_sets, 's') !== false)
            $sets[] = '!@#$%&*?';
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }
        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++)
            $password .= $all[array_rand($all)];
        $password = str_shuffle($password);
        if (!$add_dashes)
            return $password;
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;
        return $dash_str;
    }
}
