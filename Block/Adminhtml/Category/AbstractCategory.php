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
namespace Mageplaza\Blog\Block\Adminhtml\Category;

class AbstractCategory extends \Magento\Backend\Block\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * Blog Category tree model instance
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Category\Tree
     */
	public $categoryTree;

    /**
     * Blog Category factory
     *
     * @var \Mageplaza\Blog\Model\CategoryFactory
     */
	public $categoryFactory;

    /**
     * Blog Category collection factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory
     */
	public $categoryCollectionFactory;

    /**
     * constructor
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Mageplaza\Blog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
     * @param \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Mageplaza\Blog\Model\ResourceModel\Category\Tree $categoryTree,
        \Mageplaza\Blog\Model\CategoryFactory $categoryFactory,
        \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
    
        $this->coreRegistry              = $coreRegistry;
        $this->categoryTree              = $categoryTree;
        $this->categoryFactory           = $categoryFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current Blog Category instance
     *
     * @return \Mageplaza\Blog\Model\Category
     */
    public function getCategory()
    {
        return $this->coreRegistry->registry('mageplaza_blog_category');
    }

    /**
     * @return int|string|null
     */
    public function getCategoryId()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getId();
        }
        return \Mageplaza\Blog\Model\Category::TREE_ROOT_ID;
    }

    /**
     * @return string
     */
    public function getCategoryName()
    {
        return $this->getCategory()->getName();
    }

    /**
     * @return mixed
     */
    public function getCategoryPath()
    {
        if ($this->getCategory()) {
            return $this->getCategory()->getPath();
        }
        return \Mageplaza\Blog\Model\Category::TREE_ROOT_ID;
    }

    /**
     * @param null $parentNodeCategory
     * @param int $recursionLevel
     * @return Node|mixed
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getRoot($parentNodeCategory = null, $recursionLevel = 3)
    {
        if ($parentNodeCategory !== null && $parentNodeCategory->getId()) {
            return $this->getNode($parentNodeCategory, $recursionLevel);
        }
        $root = $this->coreRegistry->registry('mageplaza_blog_category_root');
        if ($root === null) {
            $rootId = \Mageplaza\Blog\Model\Category::TREE_ROOT_ID;

            $tree = $this->categoryTree->load(null, $recursionLevel);

            if ($this->getCategory()) {
                $tree->loadEnsuredNodes($this->getCategory(), $tree->getNodeById($rootId));
            }

            $tree->addCollectionData($this->getCategoryCollection());

            $root = $tree->getNodeById($rootId);

            if ($root && $rootId != \Mageplaza\Blog\Model\Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == \Mageplaza\Blog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('ROOT'));
            }

            $this->coreRegistry->register('mageplaza_blog_category_root', $root);
        }

        return $root;
    }

    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Category\Collection
     */
    public function getCategoryCollection()
    {
        $collection = $this->getData('category_collection');
        if ($collection === null) {
            $collection = $this->categoryCollectionFactory->create();
            $this->setData('category_collection', $collection);
        }
        return $collection;
    }

    /**
     * Get and register Categories root by specified Categories IDs
     *
     * IDs can be arbitrary set of any Categories ids.
     * Tree with minimal required nodes (all parents and neighbours) will be built.
     * If ids are empty, default tree with depth = 2 will be returned.
     *
     * @param array $ids
     * @return mixed
     */
    public function getRootByIds($ids)
    {
        $root = $this->coreRegistry->registry('mageplaza_blog_category_root');
        if (null === $root) {
            $ids = $this->categoryTree->getExistingCategoryIdsBySpecifiedIds($ids);
            $tree = $this->categoryTree->loadByIds($ids);
            $rootId = \Mageplaza\Blog\Model\Category::TREE_ROOT_ID;
            $root = $tree->getNodeById($rootId);
            if ($root && $rootId != \Mageplaza\Blog\Model\Category::TREE_ROOT_ID) {
                $root->setIsVisible(true);
            } elseif ($root && $root->getId() == \Mageplaza\Blog\Model\Category::TREE_ROOT_ID) {
                $root->setName(__('Root'));
            }

            $tree->addCollectionData($this->getCategoryCollection());
            $this->coreRegistry->register('mageplaza_blog_category_root', $root);
        }
        return $root;
    }

    /**
     * @param $parentNodeCategory
     * @param int $recursionLevel
     * @return Node
     */
    public function getNode($parentNodeCategory, $recursionLevel = 2)
    {
        $nodeId = $parentNodeCategory->getId();
        $node = $this->categoryTree->loadNode($nodeId);
        $node->loadChildren($recursionLevel);

        if ($node && $nodeId != \Mageplaza\Blog\Model\Category::TREE_ROOT_ID) {
            $node->setIsVisible(true);
        } elseif ($node && $node->getId() == \Mageplaza\Blog\Model\Category::TREE_ROOT_ID) {
            $node->setName(__('Root'));
        }

        $this->categoryTree->addCollectionData($this->getCategoryCollection());

        return $node;
    }

    /**
     * @param array $args
     * @return string
     */
    public function getSaveUrl(array $args = [])
    {
        $params = ['_current' => false, '_query' => false];
        $params = array_merge($params, $args);
        return $this->getUrl('mageplaza_blog/*/save', $params);
    }

    /**
     * @return string
     */
    public function getEditUrl()
    {
        return $this->getUrl(
            'mageplaza_blog/category/edit',
            ['_query' => false, 'id' => null, 'parent' => null]
        );
    }
    /**
     * @return []
     */
    public function getRootIds()
    {
        return [\Mageplaza\Blog\Model\Category::TREE_ROOT_ID];
    }
}
