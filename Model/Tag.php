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
 * @method Tag setName($name)
 * @method Tag setDescription($description)
 * @method Tag setEnabled($enabled)
 * @method mixed getName()
 * @method mixed getDescription()
 * @method mixed getEnabled()
 * @method Tag setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method Tag setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 * @method Tag setPostsData(array $data)
 * @method array getPostsData()
 * @method Tag setIsChangedPostList(\bool $flag)
 * @method bool getIsChangedPostList()
 * @method Tag setAffectedPostIds(array $ids)
 * @method bool getAffectedPostIds()
 */
class Tag extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_blog_tag';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_blog_tag';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_blog_tag';

    /**
     * Post Collection
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
	public $postCollection;

    /**
     * Post Collection Factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory
     */
	public $postCollectionFactory;

    /**
     * constructor
     *
     * @param \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
    
        $this->postCollectionFactory = $postCollectionFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }


    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mageplaza\Blog\Model\ResourceModel\Tag');
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
        $values = [];
        $values['enabled'] = '1';
        $values['store_ids'] = '1';
        return $values;
    }
    /**
     * @return array|mixed
     */
    public function getPostsPosition()
    {
        if (!$this->getId()) {
            return [];
        }
        $array = $this->getData('posts_position');
        if ($array === null) {
            $array = $this->getResource()->getPostsPosition($this);
            $this->setData('posts_position', $array);
        }
        return $array;
    }

    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getSelectedPostsCollection()
    {
        if ($this->postCollection === null) {
            $collection = $this->postCollectionFactory->create();
            $collection->join(
                'mageplaza_blog_post_tag',
                'main_table.post_id=mageplaza_blog_post_tag.post_id AND mageplaza_blog_post_tag.tag_id='.$this->getId(),
                ['position']
            );
            $this->postCollection = $collection;
        }
        return $this->postCollection;
    }
}
