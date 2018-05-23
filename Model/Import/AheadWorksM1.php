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
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Backend\Model\Auth\Session;

/**
 * Class AheadWorksM1
 * @package Mageplaza\Blog\Model\Import
 */
class AheadWorksM1 extends AbstractModel
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
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * AheadWorksM1 constructor.
     * @param Context $context
     * @param Registry $registry
     * @param PostFactory $postFactory
     * @param TagFactory $tagFactory
     * @param CategoryFactory $categoryFactory
     * @param CommentFactory $commentFactory
     * @param CustomerFactory $customerFactory
     * @param ObjectManagerInterface $objectManager
     * @param Session $authSession
     * @param ResourceConnection $resourceConnection
     * @param DateTime $date
     * @param StoreManagerInterface $storeManager
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
        CustomerFactory $customerFactory,
        ObjectManagerInterface $objectManager,
        Session $authSession,
        ResourceConnection $resourceConnection,
        DateTime $date,
        StoreManagerInterface $storeManager,
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
        $this->_customerFactory = $customerFactory;
        $this->_objectManager = $objectManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_authSession = $authSession;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param $data
     * @param $connection
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function runImport($data, $connection)
    {
        mysqli_query($connection, 'SET NAMES "utf8"');

        if ($this->importPosts($data, $connection) && $data['type'] == 'ahead_work_m1') {
            $this->importTags($data, $connection);
            $this->importCategories($data, $connection);
            $this->importComments($data, $connection);
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $data
     * @param $connection
     * @return bool
     */
    public function importPosts($data, $connection)
    {
        $authorId = $this->_authSession->getUser()->getId();
        $tablePrefix = $data["table_prefix"];
        $sqlString = "SELECT * FROM `" . $tablePrefix . "aw_blog`";

        $result = mysqli_query($connection, $sqlString);
        if ($result) {
            $errorCount = 0;
            $successCount = 0;
            $hasData = false;
            $postModel = $this->_postFactory->create();
            $deleteCount = $this->behaviour($postModel, $data);
            $oldPostIds = [];
            $tags = [];
            while ($post = mysqli_fetch_assoc($result)) {

                $createDate = (strtotime($post["created_time"]) > strtotime($this->date->date())) ? strtotime($this->date->date()) : strtotime($post["created_time"]);
                $modifyDate = strtotime($post["update_time"]);
                $publicDate = strtotime($post["created_time"]);
                $content = $post["post_content"];
                $postTag = $post["tags"];

                try {
                    $postModel->setData([
                        "name" => $post["title"],
                        "short_description" => $post["short_content"],
                        "post_content" => $content,
                        "created_at" => $createDate,
                        "updated_at" => $modifyDate,
                        "publish_date" => $publicDate,
                        "enabled" => 1,
                        "in_rss" => 0,
                        "allow_comment" => 1,
                        "store_ids" => $this->_storeManager->getStore()->getId(),
                        "meta_robots" => "INDEX,FOLLOW",
                        "meta_keywords" => $post["meta_keywords"],
                        "meta_description" => $post["meta_description"],
                        "author_id" => (int)$authorId

                    ])->save();
                    $successCount++;
                    $hasData = true;
                    $oldPostIds [$postModel->getId()] = $post["post_id"];
                    if (!empty($postTag)) {
                        $tags[$postModel->getId()] = explode(",", $postTag);
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $hasData = true;
                    continue;
                }
            }
            mysqli_free_result($result);
            $statistics = $this->getStatistics("posts", $successCount, $errorCount, $deleteCount, $hasData);
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
     */
    public function importTags($data, $connection)
    {
        $tablePrefix = $data["table_prefix"];
        $sqlString = "SELECT * FROM `" . $tablePrefix . "aw_blog_tags`";
        $result = mysqli_query($connection, $sqlString);
        $oldTagIds = [];
        $newTags = [];
        $errorCount = 0;
        $successCount = 0;
        $hasData = false;
        $tagModel = $this->_tagFactory->create();
        $deleteCount = $this->behaviour($tagModel, $data);

        while ($tag = mysqli_fetch_assoc($result)) {

            try {
                $tagModel->setData([
                    "name" => $tag["tag"],
                    "meta_robots" => "INDEX,FOLLOW",
                    "store_ids" => $this->_storeManager->getStore()->getId(),
                    "enabled" => 1
                ])->save();
                $successCount++;
                $oldTagIds[$tagModel->getId()] = $tag["id"];
                $newTags[$tagModel->getId()] = strtoupper($tag["tag"]);
                $hasData = true;

            } catch (\Exception $e) {
                $errorCount++;
                $hasData = true;
                continue;
            }
        }
        mysqli_free_result($result);

        $tags = $this->_registry->registry('mageplaza_import_post_tags_collection');
        foreach ($tags as $postId => $tagsName) {
            foreach ($tagsName as $name) {
                $newTagId = array_search(strtoupper($name), $newTags);
                $this->_resourceConnection->getConnection()
                    ->insert($this->_resourceConnection->getTableName('mageplaza_blog_post_tag'), ['tag_id' => $newTagId, 'post_id' => $postId, 'position' => 0]);
            }
        }

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
        $sqlString = "SELECT * FROM `" . $tablePrefix . "aw_blog_cat`";
        $result = mysqli_query($connection, $sqlString);
        $categoryModel = $this->_categoryFactory->create();
        $oldCategoryIds = [];
        $errorCount = 0;
        $successCount = 0;
        $hasData = false;
        $deleteCount = $this->behaviour($categoryModel, $data, 1);
        while ($category = mysqli_fetch_assoc($result)) {

            try {
                $categoryModel->setData([
                    "name" => $category["title"],
                    "url_key" => $category["identifier"],
                    "meta_robots" => "INDEX,FOLLOW",
                    "store_ids" => $this->_storeManager->getStore()->getId(),
                    "enabled" => 1,
                    "path" => '1',
                    "meta_description" => $category["meta_description"],
                    "meta_keywords" => $category["meta_keywords"]
                ])->save();
                $oldCategoryIds[$categoryModel->getId()] = $category["cat_id"];
                $successCount++;
                $hasData = true;

            } catch (\Exception $e) {
                $errorCount++;
                $hasData = true;
                continue;
            }
        }

        mysqli_free_result($result);
        $this->importCategoryPost($data, $connection, $oldCategoryIds, 'mageplaza_blog_post_category');

        $statistics = $this->getStatistics("categories", $successCount, $errorCount, $deleteCount, $hasData);
        $this->_registry->register('mageplaza_import_category_statistic', $statistics);
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
        $sqlString = "SELECT * FROM `" . $tablePrefix . "aw_blog_comment`";
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
            $createDate = strtotime($comment["created_time"]);
            switch ($comment["status"]) {
                case '2':
                    $status = 1;
                    break;
                case '1':
                    $status = 3;
                    break;
                default:
                    $status = 1;
            }

            $newPostId = array_search($comment["post_id"], $oldPostIds);
            if ($accountManage->isEmailAvailable($comment["email"], $websiteId)) {
                $entityId = 0;
                $userName = $comment["user"];
                $userEmail = $comment["email"];
            } else {
                $customerModel->setWebsiteId($websiteId);
                $customerModel->loadByEmail($comment["email"]);
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
                    "content" => $comment["comment"],
                    "created_at" => $createDate,
                    "status" => $status,
                    "store_ids" => $this->_storeManager->getStore()->getId(),
                    "user_name" => $userName,
                    "user_email" => $userEmail
                ])->save();
                $successCount++;
                $hasData = true;
                $oldCommentIds [$commentModel->getId()] = $comment["comment_id"];

            } catch (\Exception $e) {
                $errorCount++;
                $hasData = true;
                continue;
            }
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
     * @param $oldCatIds
     * @param $relationTable
     */
    public function importCategoryPost($data, $connection, $oldCatIds, $relationTable)
    {
        $tablePrefix = $data["table_prefix"];
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $categoryPostTable = $this->_resourceConnection->getTableName($relationTable);
        $data = [];
        foreach ($oldPostIds as $newPostId => $oldPostId) {

            $sqlRelation = "SELECT * FROM `" . $tablePrefix . "aw_blog_post_cat` WHERE `post_id` = " . $oldPostId;

            $result = mysqli_query($connection, $sqlRelation);

            while ($categoryPost = mysqli_fetch_assoc($result)) {
                $newCategoryId = (array_search($categoryPost["cat_id"], $oldCatIds)) ?: "1";
                $data[] = [
                    'category_id' => $newCategoryId,
                    'post_id' => $newPostId,
                    'position' => 0
                ];
            }
        }
        $this->_resourceConnection->getConnection()->insertMultiple($categoryPostTable, $data);
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

}
