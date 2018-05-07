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
     * WordPress constructor.
     * @param Context $context
     * @param Registry $registry
     * @param PostFactory $postFactory
     * @param TagFactory $tagFactory
     * @param CategoryFactory $categoryFactory
     * @param ObjectManagerInterface $objectManager
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
        ObjectManagerInterface $objectManager,
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
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
        $this->_helperImage = $helperImage;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param $data
     * @param $connection
     */
    public function runImport($data, $connection)
    {
        mysqli_query($connection, 'SET NAMES "utf8"');
        $this->importPosts($data, $connection);
        $this->importTags($data, $connection);
        $this->importCategories($data, $connection);
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
        $this->importRelationships($connection, $oldTagIds, 'mageplaza_blog_post_tag', 'post_tag');

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
        $this->importRelationships($connection, $oldCategoryIds, 'mageplaza_blog_post_category', 'category', 1);

        $statistics = $this->getStatistics("categories", $successCount, $errorCount, $deleteCount, $hasData);
        $this->_registry->register('mageplaza_import_category_statistic', $statistics);
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
     * @param $connection
     * @param $oldTermIds
     * @param $relationTable
     * @param $termType
     * @param null $isCategory
     */
    public function importRelationships($connection, $oldTermIds, $relationTable, $termType, $isCategory = null)
    {
        $oldPostIds = $this->_registry->registry('mageplaza_import_post_ids_collection');
        $resourceConnection = $this->_objectManager->create('\Magento\Framework\App\ResourceConnection')->getConnection();
        $categoryPostTable = $resourceConnection->getTableName($relationTable);

        foreach ($oldPostIds as $newPostId => $oldPostId) {

            $sqlRelation = "SELECT `wp_term_taxonomy`.`term_id` 
                          FROM `wp_term_taxonomy` 
                          INNER JOIN `wp_term_relationships` 
                          ON wp_term_taxonomy.`term_taxonomy_id`=wp_term_relationships.`term_taxonomy_id` 
                          RIGHT JOIN `wp_terms` 
                          ON wp_term_taxonomy.`term_id` = wp_terms.`term_id`
                          WHERE wp_term_taxonomy.taxonomy = '" . $termType . "' 
                          AND `wp_term_relationships`.`object_id` = " . $oldPostId;
            $result = mysqli_query($connection, $sqlRelation);
            while ($categoryPost = mysqli_fetch_assoc($result)) {
                if ($isCategory) {
                    $newCategoryId = (array_search($categoryPost["term_id"], $oldTermIds)) ?: "1";
                    $termId = 'category_id';
                } else {
                    $newCategoryId = array_search($categoryPost["term_id"], $oldTermIds);
                    $termId = 'tag_id';
                }
                $resourceConnection->query(
                    "Insert INTO " . $categoryPostTable . " (" . $termId . ",post_id,position)
                    VALUES (" . $newCategoryId . "," . $newPostId . ",0) "
                );
            }
            mysqli_free_result($result);
        }
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
