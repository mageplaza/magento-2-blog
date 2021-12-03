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

namespace Mageplaza\Blog\Model;

use Exception;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\Post as PostResource;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;
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
 * @method Post setCreatedAt(string $createdAt)
 * @method string getCreatedAt()
 * @method Post setUpdatedAt(string $updatedAt)
 * @method string getUpdatedAt()
 * @method Post setTagsData(array $data)
 * @method Post setTopicsData(array $data)
 * @method Post setProductsData(array $data)
 * @method array getTagsData()
 * @method array getProductsData()
 * @method array getTopicsData()
 * @method Post setIsChangedTagList(bool $flag)
 * @method Post setIsChangedProductList(bool $flag)
 * @method Post setIsChangedTopicList(bool $flag)
 * @method Post setIsChangedCategoryList(bool $flag)
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
 * @method array getTagsIds()
 * @method Post setTagsIds(array $tagIds)
 * @method array getTopicsIds()
 * @method Post setTopicsIds(array $topicIds)
 */
class Post extends AbstractModel
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
     * @var ResourceModel\Tag\Collection
     */
    public $tagCollection;

    /**
     * Topic Collection
     *
     * @var ResourceModel\Topic\Collection
     */
    public $topicCollection;

    /**
     * Blog Category Collection
     *
     * @var ResourceModel\Category\Collection
     */
    public $categoryCollection;

    /**
     * Tag Collection Factory
     *
     * @var CollectionFactory
     */
    public $tagCollectionFactory;

    /**
     * Topic Collection Factory
     *
     * @var TopicCollectionFactory
     */
    public $topicCollectionFactory;

    /**
     * Blog Category Collection Factory
     *
     * @var CategoryCollectionFactory
     */
    public $categoryCollectionFactory;

    /**
     * Post Collection Factory
     *
     * @var PostCollectionFactory
     */
    public $postCollectionFactory;

    /**
     * Related Post Collection
     *
     * @var Collection
     */
    public $relatedPostCollection;

    /**
     * Previous Post Collection
     *
     * @var Collection
     */
    public $prevPostCollection;

    /**
     * Next Post Collection
     *
     * @var Collection
     */
    public $nextPostCollection;

    /**
     * @var DateTime
     */
    public $dateTime;

    /**
     * @var Data
     */
    public $helperData;

    /**
     * @var ProductCollectionFactory
     */
    public $productCollectionFactory;

    /**
     * @var ProductCollection
     */
    public $productCollection;

    /**
     * @var TrafficFactory
     */
    protected $trafficFactory;

    /**
     * Post constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Data $helperData
     * @param TrafficFactory $trafficFactory
     * @param CollectionFactory $tagCollectionFactory
     * @param TopicCollectionFactory $topicCollectionFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param PostCollectionFactory $postCollectionFactory
     * @param ProductCollectionFactory $productCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
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
    ) {
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
        $this->_init(PostResource::class);
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
     *
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
     * @param null $store
     *
     * @return string
     */
    public function getUrl($store = null)
    {
        return $this->helperData->getBlogUrl($this, Data::TYPE_POST, $store);
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
     * @return ResourceModel\Tag\Collection
     */
    public function getSelectedTagsCollection()
    {
        if ($this->tagCollection === null) {
            $collection = $this->tagCollectionFactory->create();
            $collection->getSelect()->join(
                $this->getResource()->getTable('mageplaza_blog_post_tag'),
                'main_table.tag_id=' . $this->getResource()->getTable('mageplaza_blog_post_tag') . '.tag_id AND '
                . $this->getResource()->getTable('mageplaza_blog_post_tag') . '.post_id=' . $this->getId(),
                ['position']
            )->where("main_table.enabled='1'");
            $this->tagCollection = $collection;
        }

        return $this->tagCollection;
    }

    /**
     * @return ResourceModel\Topic\Collection
     */
    public function getSelectedTopicsCollection()
    {
        if ($this->topicCollection === null) {
            $collection = $this->topicCollectionFactory->create();
            $collection->join(
                $this->getResource()->getTable('mageplaza_blog_post_topic'),
                'main_table.topic_id=' . $this->getResource()->getTable('mageplaza_blog_post_topic') . '.topic_id AND '
                . $this->getResource()->getTable('mageplaza_blog_post_topic') . '.post_id=' . $this->getId(),
                ['position']
            );
            $this->topicCollection = $collection;
        }

        return $this->topicCollection;
    }

    /**
     * @return ResourceModel\Category\Collection
     */
    public function getSelectedCategoriesCollection()
    {
        if ($this->categoryCollection === null) {
            $collection = $this->categoryCollectionFactory->create();
            $collection->join(
                $this->getResource()->getTable('mageplaza_blog_post_category'),
                'main_table.category_id=' . $this->getResource()->getTable('mageplaza_blog_post_category') .
                '.category_id AND ' . $this->getResource()->getTable('mageplaza_blog_post_category') . '.post_id="'
                . $this->getId() . '"',
                ['position']
            );
            $this->categoryCollection = $collection;
        }

        return $this->categoryCollection;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getCategoryIds()
    {
        if (!$this->hasData('category_ids')) {
            $ids = $this->_getResource()->getCategoryIds($this);
            $this->setData('category_ids', $ids);
        }

        return (array) $this->_getData('category_ids');
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getTagIds()
    {
        if (!$this->hasData('tag_ids')) {
            $ids = $this->_getResource()->getTagIds($this);

            $this->setData('tag_ids', $ids);
        }

        return (array) $this->_getData('tag_ids');
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getTopicIds()
    {
        if (!$this->hasData('topic_ids')) {
            $ids = $this->_getResource()->getTopicIds($this);

            $this->setData('topic_ids', $ids);
        }

        return (array) $this->_getData('topic_ids');
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getViewTraffic()
    {
        if (!$this->hasData('view_traffic')) {
            $traffic = $this->_getResource()->getViewTraffic($this);

            $this->setData('view_traffic', $traffic[0]);
        }

        return $this->_getData('view_traffic');
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getAuthorName()
    {
        if (!$this->hasData('author_name')) {
            $author = $this->_getResource()->getAuthor($this);

            $this->setData('author_name', $author['name']);
        }

        return $this->_getData('author_name');
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getAuthorUrl()
    {
        if (!$this->hasData('author_url')) {
            $author = $this->_getResource()->getAuthor($this);

            $this->setData('author_url', $this->helperData->getBlogUrl($author['url_key'], Data::TYPE_AUTHOR));
        }

        return $this->_getData('author_url');
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    public function getAuthorUrlKey()
    {
        if (!$this->hasData('author_url_key')) {
            $author = $this->_getResource()->getAuthor($this);

            $this->setData('author_url_key', $author['url_key']);
        }

        return $this->_getData('author_url_key');
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getUrlImage()
    {
        $imageHelper = $this->helperData->getImageHelper();
        $imageFile   = $this->getImage() ? $imageHelper->getMediaPath($this->getImage(), 'post') : '';
        $imageUrl    = $imageFile ? $this->helperData->getImageHelper()->getMediaUrl($imageFile) : '';

        $this->setData('image', $imageUrl);

        return $this->_getData('image');
    }

    /**
     * @throws Exception
     */
    public function updateViewTraffic()
    {
        if ($this->getId()) {
            $trafficModel = $this->trafficFactory->create()->load($this->getId(), 'post_id');

            if ($trafficModel->getId()) {
                $trafficModel->setNumbersView($trafficModel->getNumbersView() + 1);
                $trafficModel->save();
            } else {
                $traffic = $this->trafficFactory->create();
                $traffic->addData(['post_id' => $this->getId(), 'numbers_view' => 1])->save();
            }
        }
    }

    /**
     * @return Collection|null
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getRelatedPostsCollection()
    {
        $topicIds = $this->_getResource()->getTopicIds($this);
        if (count($topicIds)) {
            $collection = $this->postCollectionFactory->create();
            $collection->getSelect()
                ->join(
                    ['topic' => $this->getResource()->getTable('mageplaza_blog_post_topic')],
                    'main_table.post_id=topic.post_id AND topic.post_id != "' . $this->getId()
                    . '" AND topic.topic_id IN (' . implode(',', $topicIds) . ')',
                    ['position']
                )->group('main_table.post_id');

            if ($limit = (int) $this->helperData->getBlogConfig('general/related_post')) {
                $collection->getSelect()
                    ->limit($limit);
            }
            $collection->addFieldToFilter('enabled', '1');
            $this->helperData->addStoreFilter($collection);

            return $collection;
        }

        return null;
    }

    /**
     * @return ProductCollection
     */
    public function getSelectedProductsCollection()
    {
        if ($this->productCollection === null) {
            $collection = $this->productCollectionFactory->create();
            $collection->getSelect()->join(
                $this->getResource()->getTable('mageplaza_blog_post_product'),
                'e.entity_id=' . $this->getResource()->getTable('mageplaza_blog_post_product')
                . '.entity_id AND ' . $this->getResource()->getTable('mageplaza_blog_post_product') . '.post_id='
                . $this->getId(),
                ['position']
            );
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
     * @return Collection
     */
    public function getPrevPost()
    {
        if ($this->prevPostCollection === null) {
            $collection = $this->postCollectionFactory->create();
            $collection->addFieldToFilter('post_id', ['lt' => $this->getId()])
                ->setOrder('post_id', 'DESC')->setPageSize(1)->setCurPage(1);
            $this->prevPostCollection = $collection;
        }

        return $this->prevPostCollection;
    }

    /**
     * get next post
     * @return Collection
     */
    public function getNextPost()
    {
        if ($this->nextPostCollection === null) {
            $collection = $this->postCollectionFactory->create();
            $collection->addFieldToFilter('post_id', ['gt' => $this->getId()])
                ->setOrder('post_id', 'ASC')->setPageSize(1)->setCurPage(1);
            $this->nextPostCollection = $collection;
        }

        return $this->nextPostCollection;
    }
}
