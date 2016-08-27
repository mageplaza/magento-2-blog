<?php
/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 *                     @category  Mageplaza
 *                     @package   Mageplaza_Blog
 *                     @copyright Copyright (c) 2016
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Model;

/**
 * @method Category setName($name)
 * @method Category setDescription($description)
 * @method Category setUrlKey($urlKey)
 * @method Category setEnabled($enabled)
 * @method Category setMetaTitle($metaTitle)
 * @method Category setMetaDescription($metaDescription)
 * @method Category setMetaKeywords($metaKeywords)
 * @method Category setMetaRobots($metaRobots)
 * @method mixed getName()
 * @method mixed getDescription()
 * @method mixed getUrlKey()
 * @method mixed getEnabled()
 * @method mixed getMetaTitle()
 * @method mixed getMetaDescription()
 * @method mixed getMetaKeywords()
 * @method mixed getMetaRobots()
 * @method Category setParentId(\int $parentId)
 * @method int getParentId()
 * @method Category setPath(\string $path)
 * @method string getPath()
 * @method Category setPosition(\int $path)
 * @method int getPosition()
 * @method Category setChildrenCount(\int $path)
 * @method int getChildrenCount()
 * @method Category setCreatedAt(\string $createdAt)
 * @method string getCreatedAt()
 * @method Category setUpdatedAt(\string $updatedAt)
 * @method string getUpdatedAt()
 * @method Category setMovedCategoryId(\string $id)
 * @method Category setAffectedCategoryIds(array $ids)
 * @method Category setPostsData(array $data)
 * @method array getPostsData()
 * @method Category setIsChangedPostList(\bool $flag)
 * @method bool getIsChangedPostList()
 * @method Category setAffectedPostIds(array $ids)
 * @method bool getAffectedPostIds()
 */
class Category extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Root of the Category tree
     *
     * @var string
     */
    const TREE_ROOT_ID = 1;

    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_blog_category';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_blog_category';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_blog_category';

    /**
     * Post Collection
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    protected $postCollection;

    /**
     * Category Factory
     *
     * @var \Mageplaza\Blog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Post Collection Factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    protected $postCollectionFactory;

    /**
     * constructor
     *
     * @param \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
     * @param \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Mageplaza\Blog\Model\CategoryFactory $categoryFactory,
        \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
    
        $this->categoryFactory       = $categoryFactory;
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
        $this->_init('Mageplaza\Blog\Model\ResourceModel\Category');
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

        return $values;
    }

    /**
     * get tree path ids
     *
     * @return array
     */
    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if ($ids === null) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }
        return $ids;
    }

    /**
     * get all parent ids
     *
     * @return array
     */
    public function getParentIds()
    {
        return array_diff($this->getPathIds(), [$this->getId()]);
    }

    /**
     * move Category in tree
     *
     * @param $parentId
     * @param $afterCategoryId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    public function move($parentId, $afterCategoryId)
    {
        try {
            $parent = $this->categoryFactory->create()->load($parentId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, but we can\'t move the Category because we can\'t find the new parent Category you selected.'),
                $e
            );
        }

        if (!$this->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Sorry, but we can\'t move the Category because we can\'t find the new parent Category you selected.')
            );
        } elseif ($parent->getId() == $this->getId()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'We can\'t perform this Category move operation because the parent Category matches the child Category.'
                )
            );
        }

        $this->setMovedCategoryId($this->getId());
        $oldParentId = $this->getParentId();
        $oldParentIds = $this->getParentIds();

        $eventParams = [
            $this->_eventObject => $this,
            'parent' => $parent,
            'category_id' => $this->getId(),
            'prev_parent_id' => $oldParentId,
            'parent_id' => $parentId,
        ];

        $this->_getResource()->beginTransaction();
        try {
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_before', $eventParams);
            $this->getResource()->changeParent($this, $parent, $afterCategoryId);
            $this->_eventManager->dispatch($this->_eventPrefix . '_move_after', $eventParams);
            $this->_getResource()->commit();

            // Set data for indexer
            $this->setAffectedCategoryIds([$this->getId(), $oldParentId, $parentId]);
        } catch (\Exception $e) {
            $this->_getResource()->rollBack();
            throw $e;
        }
        $this->_eventManager->dispatch($this->_eventPrefix . '_move', $eventParams);
        $this->_cacheManager->clean([self::CACHE_TAG]);

        return $this;
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
        if (is_null($array)) {
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
        if (is_null($this->postCollection)) {
            $collection = $this->postCollectionFactory->create();
            $collection->join(
                'mageplaza_blog_post_category',
                'main_table.post_id=mageplaza_blog_post_category.post_id AND mageplaza_blog_post_category.category_id='.$this->getId(),
                ['position']
            );
            $this->postCollection = $collection;
        }
        return $this->postCollection;
    }
}
