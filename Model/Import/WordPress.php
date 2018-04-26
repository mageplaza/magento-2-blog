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
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
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
     * WordPress constructor.
     * @param Context $context
     * @param Registry $registry
     * @param PostFactory $postFactory
     * @param TagFactory $tagFactory
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
        $this->importPosts($data, $connection);
        $this->importTags($data, $connection);

    }

    /**
     * @param $data
     * @param $connection
     */
    public function importPosts($data, $connection)
    {
        $sqlString = "SELECT * FROM `wp_posts` WHERE post_type = 'post' AND post_status <> 'auto-draft'";
        $result = mysqli_query($connection, $sqlString);
        $errorCount = 0;
        $successCount = 0;
        $deleteCount = 0;
        if ($data["behaviour"] == "replace") {
            $postCollection = $this->_postFactory->create()->getCollection();

            foreach ($postCollection as $item) {
                $item->delete();
                $deleteCount++;
            }
        }
        while ($post = mysqli_fetch_assoc($result)) {
            $createDate = (strtotime($post["post_date_gmt"]) > strtotime($this->date->date())) ? strtotime($this->date->date()) : strtotime($post["post_date_gmt"]);
            $modifyDate = strtotime($post["post_modified_gmt"]);
            $publicDate = strtotime($post["post_date_gmt"]);
            $content = $post["post_content"];
            $content = preg_replace("/(http:\/\/)(.+?)(\/wp-content\/)/", $this->_helperImage->getBaseMediaUrl() . "/wysiwyg/", $content);

            try {
                $this->_postFactory->create()->setData([
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
            } catch (\Exception $e) {
                $errorCount++;
                continue;
            }

        }
        $statistics = [
            "type" => "posts",
            "success_count" => $successCount,
            "error_count" => $errorCount,
            "delete_count" => $deleteCount
        ];
        $this->_registry->register('mageplaza_import_post_statistic', $statistics);

    }

    /**
     * @param $data
     * @param $connection
     */
    public function importTags($data, $connection)
    {
        $sqlString = "SELECT * FROM `wp_terms` 
                          INNER JOIN `wp_term_taxonomy` 
                          ON wp_terms.term_id=wp_term_taxonomy.term_id 
                          WHERE wp_term_taxonomy.taxonomy = 'post_tag'";
        $result = mysqli_query($connection, $sqlString);
        $errorCount = 0;
        $successCount = 0;
        $deleteCount = 0;

        if ($data["behaviour"] == "replace") {
            $tagCollection = $this->_tagFactory->create()->getCollection();

            foreach ($tagCollection as $item) {
                $item->delete();
                $deleteCount++;
            }
        }
        while ($tag = mysqli_fetch_assoc($result)) {
            try {
                $this->_tagFactory->create()->setData([
                    "name" => $tag["name"],
                    "url_key" => $tag["slug"],
                    "description" => $tag["description"],
                    "meta_robots" => "INDEX,FOLLOW",
                    "store_ids" => $this->_storeManager->getStore()->getId(),
                    "enabled" => 1
                ])->save();
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                continue;
            }
        }
        $statistics = [
            "type" => "tags",
            "success_count" => $successCount,
            "error_count" => $errorCount,
            "delete_count" => $deleteCount
        ];
        $this->_registry->register('mageplaza_import_tag_statistic', $statistics);
    }
}
