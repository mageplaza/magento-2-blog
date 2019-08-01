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
use Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;

/**
 * @method Tag setName($name)
 * @method Tag setDescription($description)
 * @method Tag setEnabled($enabled)
 * @method mixed getName()
 * @method mixed getDescription()
 * @method mixed getEnabled()
 * @method Tag setCreatedAt(string $createdAt)
 * @method string getCreatedAt()
 * @method Tag setUpdatedAt(string $updatedAt)
 * @method string getUpdatedAt()
 * @method Tag setPostsData(array $data)
 * @method array getPostsData()
 * @method Tag setIsChangedPostList(bool $flag)
 * @method bool getIsChangedPostList()
 * @method Tag setAffectedPostIds(array $ids)
 * @method bool getAffectedPostIds()
 */
class Tag extends AbstractModel
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
     * @var TagCollectionFactory
     */
    public $tagCollectionFactory;

    /**
     * Tag constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $postCollectionFactory
     * @param TagCollectionFactory $tagCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $postCollectionFactory,
        TagCollectionFactory $tagCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->postCollectionFactory = $postCollectionFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Tag::class);
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
     * @return array|mixed
     */
    public function getPostsPosition()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('posts_position');
        if (!$array) {
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
                ['post_tag' => $this->getResource()->getTable('mageplaza_blog_post_tag')],
                'main_table.post_id=post_tag.post_id AND post_tag.tag_id=' . $this->getId(),
                ['position']
            );

            $this->postCollection = $collection;
        }

        return $this->postCollection;
    }
}
