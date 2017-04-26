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
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Blog\Model;

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
 * @method mixed getShortDescription()
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
 * @method array getTagsData()
 * @method array getTopicsData()
 * @method Post setIsChangedTagList(\bool $flag)
 * @method Post setIsChangedTopicList(\bool $flag)
 * @method Post setIsChangedCategoryList(\bool $flag)
 * @method bool getIsChangedTagList()
 * @method bool getIsChangedTopicList()
 * @method bool getIsChangedCategoryList()
 * @method Post setAffectedTagIds(array $ids)
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

	public $dateTime;
	public $helperData;
    /**
     * constructor
     *
     * @param \Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory
     * @param \Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory $topicCollectionFactory
     * @param \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory,
        \Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory $topicCollectionFactory,
        \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
		\Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
		\Mageplaza\Blog\Helper\Data $helperData,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->tagCollectionFactory      = $tagCollectionFactory;
        $this->topicCollectionFactory    = $topicCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->postCollectionFactory     = $postCollectionFactory;
		$this->helperData = $helperData;
        $this->dateTime = $dateTime;
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
        $values['enabled'] = '1';
        $values['allow_comment'] = '1';
        $values['store_ids'] = '1';

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
                'mageplaza_blog_post_tag',
                'main_table.tag_id=mageplaza_blog_post_tag.tag_id AND mageplaza_blog_post_tag.post_id='
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
                'mageplaza_blog_post_topic',
                'main_table.topic_id=mageplaza_blog_post_topic.topic_id AND mageplaza_blog_post_topic.post_id='
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
                'mageplaza_blog_post_category',
                'main_table.category_id=mageplaza_blog_post_category.category_id 
                AND mageplaza_blog_post_category.post_id="' . $this->getId().'"',
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
     * @return array
     */
    public function getTopicIds()
    {
        if (!$this->hasData('topic_ids')) {
            $ids = $this->_getResource()->getTopicIds($this);
            $this->setData('topic_ids', $ids);
        }

        return (array)$this->_getData('topic_ids');
    }

    /**
     * get category id string
     * @return mixed
     */
    public function getTopicSting()
    {
        if ($this->getTopicIds()) {
            return implode(',', $this->getTopicIds());
        } else {
            return '';
        }
    }

    /**
     * get related posts
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getRelatedPostsCollection()
    {
        if ($this->getTopicSting()) {
            $collection = $this->postCollectionFactory->create();

            $collection->getSelect()->join(
                'mageplaza_blog_post_topic',
                'main_table.post_id=mageplaza_blog_post_topic.post_id AND mageplaza_blog_post_topic.post_id != "'
				. $this->getId() . '" AND mageplaza_blog_post_topic.topic_id IN (' . $this->getTopicSting() . ')',
                ['position']
            )->group('main_table.post_id');
            $this->relatedPostCollection = $collection;
        }
        return $this->relatedPostCollection;
    }
}
