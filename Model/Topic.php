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

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;
use Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory as TopicCollectionFactory;

/**
 * @method Topic setName($name)
 * @method Topic setDescription($description)
 * @method Topic setEnabled($enabled)
 * @method Topic setUrlKey($urlKey)
 * @method Topic setMetaTitle($metaTitle)
 * @method Topic setMetaDescription($metaDescription)
 * @method Topic setMetaKeywords($metaKeywords)
 * @method Topic setMetaRobots($metaRobots)
 * @method mixed getName()
 * @method mixed getDescription()
 * @method mixed getEnabled()
 * @method mixed getUrlKey()
 * @method mixed getMetaTitle()
 * @method mixed getMetaDescription()
 * @method mixed getMetaKeywords()
 * @method mixed getMetaRobots()
 * @method Topic setCreatedAt(string $createdAt)
 * @method string getCreatedAt()
 * @method Topic setUpdatedAt(string $updatedAt)
 * @method string getUpdatedAt()
 * @method Topic setPostsData(array $data)
 * @method array getPostsData()
 * @method Topic setIsChangedPostList(bool $flag)
 * @method bool getIsChangedPostList()
 * @method Topic setAffectedPostIds(array $ids)
 * @method bool getAffectedPostIds()
 */
class Topic extends AbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_blog_topic';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_blog_topic';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_blog_topic';

    /**
     * Post Collection
     *
     * @var Collection
     */
    public $postCollection;

    /**
     * Post Collection Factory
     *
     * @var CollectionFactory
     */
    public $postCollectionFactory;

    /**
     * Topic Collection Factory
     *
     * @var TopicCollectionFactory
     */
    public $topicCollectionFactory;

    /**
     * Topic constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $postCollectionFactory
     * @param TopicCollectionFactory $topicCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $postCollectionFactory,
        TopicCollectionFactory $topicCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->postCollectionFactory = $postCollectionFactory;
        $this->topicCollectionFactory = $topicCollectionFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Topic::class);
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
     * @return Collection
     */
    public function getSelectedPostsCollection()
    {
        if ($this->postCollection === null) {
            $collection = $this->postCollectionFactory->create();
            $collection->join(
                ['topic' => $this->getResource()->getTable('mageplaza_blog_post_topic')],
                'main_table.post_id=topic.post_id AND topic.topic_id=' . $this->getId(),
                ['position']
            );
            $this->postCollection = $collection;
        }

        return $this->postCollection;
    }
}
