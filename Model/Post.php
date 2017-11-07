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
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Model;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory as PostCollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory as TopicCollectionFactory;

/**
 * @method Post setName($name)
 * @method Post setShortDescription($shortDescription)
 * @method Post setPostContent($postContent)
 * @method Post setImage($image)
 * @method Post setViews($views)
 * @method Post setEnabled($enabled)
 * @method Post setUrlKey($urlKey)
 * @method Post setInRss($inRss)
 * @method Post setAllowComment($allowComment)
 * @method Post setMetaTitle($metaTitle)
 * @method Post setMetaDescription($metaDescription)
 * @method Post setMetaKeywords($metaKeywords)
 * @method Post setMetaRobots($metaRobots)
 * @method mixed getName()
 * @method mixed getPostContent()
 * @method mixed getImage()
 * @method mixed getViews()
 * @method mixed getEnabled()
 * @method mixed getUrlKey()
 * @method mixed getInRss()
 * @method mixed getAllowComment()
 * @method mixed getMetaTitle()
 * @method mixed getMetaDescription()
 * @method mixed getMetaKeywords()
 * @method mixed getMetaRobots()
 * @method Post setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method Post setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 * @method Post setTagsData(array $data)
 * @method Post setTopicsData(array $data)
 * @method Post setProductsData(array $data)
 * @method array getTagsData()
 * @method array getProductsData()
 * @method array getTopicsData()
 * @method Post setIsChangedTagList(\bool $flag)
 * @method Post setIsChangedProductList(\bool $flag)
 * @method Post setIsChangedTopicList(\bool $flag)
 * @method Post setIsChangedCategoryList(\bool $flag)
 * @method bool getIsChangedTagList()
 * @method bool getIsChangedTopicList()
 * @method bool getIsChangedCategoryList()
 * @method Post setAffectedTagIds(array $ids)
 * @method Post setAffectedEntityIds(array $ids)
 * @method Post setAffectedTopicIds(array $ids)
 * @method Post setAffectedCategoryIds(array $ids)
 * @method bool getAffectedTagIds()
 * @method bool getAffectedTopicIds()
 * @method bool getAffectedCategoryIds()
 * @method array getCategoriesIds()
 * @method Post setCategoriesIds(array $categoryIds)
 */
class Post extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_blog_post';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_blog_post';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_blog_post';

    /**
     * Tag Collection
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Tag\Collection
     */
    public $tagCollection;

    /**
     * Topic Collection
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Topic\Collection
     */
    public $topicCollection;

    /**
     * Blog Category Collection
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Category\Collection
     */
    public $categoryCollection;

    /**
     * Tag Collection Factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory
     */
    public $tagCollectionFactory;

    /**
     * Topic Collection Factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory
     */
    public $topicCollectionFactory;

    /**
     * Blog Category Collection Factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory
     */
    public $categoryCollectionFactory;

    /**
     * Post Collection Factory
     * @type \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    public $postCollectionFactory;


    /**
     * Related Post Collection
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public $relatedPostCollection;

    /**
     * Previous Post Collection
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public $prevPostCollection;

    /**
     * Next Post Collection
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public $nextPostCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    public $dateTime;

    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    public $helperData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    public $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public $productCollection;

    /**
     * @var \Mageplaza\Blog\Model\TrafficFactory
     */
    protected $trafficFactory;

    /**
     * Post constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Mageplaza\Blog\Helper\Data $helperData
     * @param \Mageplaza\Blog\Model\TrafficFactory $trafficFactory
     * @param \Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory
     * @param \Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory $topicCollectionFactory
     * @param \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        Data $helperData,
        TrafficFactory $trafficFactory,
        CollectionFactory $tagCollectionFactory,
        TopicCollectionFactory $topicCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        PostCollectionFactory $postCollectionFactory,
        ProductCollectionFactory $productCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        $this->tagCollectionFactory      = $tagCollectionFactory;
        $this->topicCollectionFactory    = $topicCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->postCollectionFactory     = $postCollectionFactory;
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->helperData                = $helperData;
        $this->dateTime                  = $dateTime;
        $this->trafficFactory            = $trafficFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mageplaza\Blog\Model\ResourceModel\Post');
    }

    /**
     * @inheritdoc
     */
    public function afterSave()
    {
        if ($this->isObjectNew()) {
            $trafficModel = $this->trafficFactory->create()
                ->load($this->getId(), 'post_id');
            if (!$trafficModel->getId()) {
                $trafficModel->setData([
                    'post_id'      => $this->getId(),
                    'numbers_view' => 0
                ])->save();
            }
        }

        return parent::afterSave();
    }

    /**
     * @param bool $shorten
     * @return mixed|string
     */
    public function getShortDescription($shorten = false)
    {
        $shortDescription = $this->getData('short_description');

        $maxLength = 200;
        if ($shorten && strlen($shortDescription) > $maxLength) {
            $shortDescription = substr($shortDescription, 0, $maxLength) . '...';
        }

        return $shortDescription;
    }

    /**
     * @return bool|string
     */
    public function getUrl()
    {
        return $this->helperData->getBlogUrl($this, Data::TYPE_POST);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * get entity default values
     *
     * @return array
     */
    public function getDefaultValues()
    {
        $values                  = [];
        $values['in_rss']        = '1';
        $values['enabled']       = '1';
        $values['allow_comment'] = '1';
        $values['store_ids']     = '1';

        return $values;
    }

    /**
     * @return array|mixed
     */
    public function getTagsPosition()
    {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('tags_position');
        if ($array === null) {
            $array = $this->getResource()->getTagsPosition($this);
            $this->setData('tags_position', $array);
        }

        return $array;
    }

    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Tag\Collection
     */
    public function getSelectedTagsCollection()
    {
        if ($this->tagCollection === null) {
            $collection = $this->tagCollectionFactory->create();
            $collection->getSelect()->join(
                $this->getResource()->getTable('mageplaza_blog_post_tag'),
                'main_table.tag_id=' . $this->getResource()->getTable('mageplaza_blog_post_tag') . '.tag_id AND ' . $this->getResource()->getTable('mageplaza_blog_post_tag') . '.post_id='
                . $this->getId(),
                ['position']
            )->where("main_table.enabled='1'");
            $this->tagCollection = $collection;
        }

        return $this->tagCollection;
    }

    /**
     * @return array|mixed
     */
    public function getTopicsPosition()
    {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('topics_position');
        if ($array === null) {
            $array = $this->getResource()->getTopicsPosition($this);
            $this->setData('topics_position', $array);
        }

        return $array;
    }

    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Topic\Collection
     */
    public function getSelectedTopicsCollection()
    {
        if ($this->topicCollection === null) {
            $collection = $this->topicCollectionFactory->create();
            $collection->join(
                $this->getResource()->getTable('mageplaza_blog_post_topic'),
                'main_table.topic_id=' . $this->getResource()->getTable('mageplaza_blog_post_topic') . '.topic_id AND ' . $this->getResource()->getTable('mageplaza_blog_post_topic') . '.post_id='
                . $this->getId(),
                ['position']
            );
            $this->topicCollection = $collection;
        }

        return $this->topicCollection;
    }

    /**
     * @return array|mixed
     */
    public function getCategoriesPosition()
    {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('categories_position');
        if ($array === null) {
            $array = $this->getResource()->getCategoriesPosition($this);
            $this->setData('categories_position', $array);
        }

        return $array;
    }

    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Category\Collection
     */
    public function getSelectedCategoriesCollection()
    {
        if ($this->categoryCollection === null) {
            $collection = $this->categoryCollectionFactory->create();
            $collection->join(
                $this->getResource()->getTable('mageplaza_blog_post_category'),
                'main_table.category_id=' . $this->getResource()->getTable('mageplaza_blog_post_category') . '.category_id 
                AND ' . $this->getResource()->getTable('mageplaza_blog_post_category') . '.post_id="' . $this->getId() . '"',
                ['position']
            );
            $this->categoryCollection = $collection;
        }

        return $this->categoryCollection;
    }

    /**
     * @return array
     */
    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $ids = $this->_getResource()->getCategoryIds($this);
            $this->setData('category_ids', $ids);
        }

        return (array)$this->_getData('category_ids');
    }

    /**
     * @param null $limit
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection|null
     */
    public function getRelatedPostsCollection($limit = null)
    {
        $topicIds = $this->_getResource()->getTopicIds($this);
        if (sizeof($topicIds)) {
            $collection = $this->postCollectionFactory->create();
            $collection->getSelect()
                ->join(
                    ['topic' => $this->getResource()->getTable('mageplaza_blog_post_topic')],
                    'main_table.post_id=topic.post_id AND topic.post_id != "' . $this->getId() . '" AND topic.topic_id IN (' . implode(',', $topicIds) . ')',
                    ['position']
                )->group('main_table.post_id');

            if ($limit = (int)$this->helperData->getBlogConfig('general/related_post')) {
                $collection->getSelect()
                    ->limit($limit);
            }

            return $collection;
        }

        return null;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getSelectedProductsCollection()
    {
        if ($this->productCollection === null) {
            $collection = $this->productCollectionFactory->create();
            $collection->getSelect()->join(
                $this->getResource()->getTable('mageplaza_blog_post_product'),
                'main_table.entity_id=' . $this->getResource()->getTable('mageplaza_blog_post_product') . '.entity_id AND ' . $this->getResource()->getTable('mageplaza_blog_post_product') . '.post_id='
                . $this->getId(),
                ['position']
            )->where("main_table.enabled='1'");
            $this->productCollection = $collection;
        }

        return $this->productCollection;
    }

    /**
     * @return array|mixed
     */
    public function getProductsPosition()
    {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('products_position');
        if ($array === null) {
            $array = $this->getResource()->getProductsPosition($this);
            $this->setData('products_position', $array);
        }

        return $array;
    }

    /**
     * get previous post
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getPrevPost()
    {
        if ($this->prevPostCollection === null) {
            $collection = $this->postCollectionFactory->create();
            $collection->addFieldToFilter('post_id', ['lt' => $this->getId()])->setOrder('post_id', 'DESC')->setPageSize(1)->setCurPage(1);
            $this->prevPostCollection = $collection;
        }

        return $this->prevPostCollection;
    }

    /**
     * get next post
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getNextPost()
    {
        if ($this->nextPostCollection === null) {
            $collection = $this->postCollectionFactory->create();
            $collection->addFieldToFilter('post_id', ['gt' => $this->getId()])->setOrder('post_id', 'ASC')->setPageSize(1)->setCurPage(1);
            $this->nextPostCollection = $collection;
        }

        return $this->nextPostCollection;
    }
}
