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

/**
 * @method Tree setUseAjax($useAjax)
 * @method bool getUseAjax()
 */
class Tree extends \Mageplaza\Blog\Block\Adminhtml\Category\AbstractCategory
{
    /**
     * Tree template
     *
     * @var string
     */
    protected $_template = 'category/tree.phtml';

    /**
     * JSON Encoder instance
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    public $jsonEncoder;

    /**
     * Backend Session
     *
     * @var \Magento\Backend\Model\Auth\Session
     */
	public $backendSession;

    /**
     * Resource Helper
     *
     * @var \Magento\Framework\DB\Helper
     */
	public $resourceHelper;

    /**
     * constructor
     *
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $backendSession
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Mageplaza\Blog\Model\ResourceModel\Category\Tree $categoryTree
     * @param \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
     * @param \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $backendSession,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Framework\Registry $registry,
        \Mageplaza\Blog\Model\ResourceModel\Category\Tree $categoryTree,
        \Mageplaza\Blog\Model\CategoryFactory $categoryFactory,
        \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->jsonEncoder    = $jsonEncoder;
        $this->backendSession = $backendSession;
        $this->resourceHelper = $resourceHelper;
        parent::__construct($registry, $categoryTree, $categoryFactory, $categoryCollectionFactory, $context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setUseAjax(0);
    }

    /**
     * Add buttons
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $addUrl = $this->getUrl("*/*/new", ['_current' => true, 'category_id' => null, '_query' => false]);

        $this->addChild(
            'add_sub_button',
            'Magento\Backend\Block\Widget\Button',
            [
                'label' => __('Add Child Category'),
                'onclick' => "addNew('" . $addUrl . "', false)",
                'class' => 'add',
                'id' => 'add_child_category_button',
                'style' => $this->canAddChildCategory() ? '' : 'display: none;'
            ]
        );

        if ($this->canAddChildCategory()) {
            $this->addChild(
                'add_root_button',
                'Magento\Backend\Block\Widget\Button',
                [
                    'label' => __('Add Root Category'),
                    'onclick' => "addNew('" . $addUrl . "', true)",
                    'class' => 'add',
                    'id' => 'add_root_category_button'
                ]
            );
        }
        return parent::_prepareLayout();
    }

    /**
     * @param $namePart
     * @return string
     */
    public function getSuggestedCategoriesJson($namePart)
    {
        /* @var $collection \Mageplaza\Blog\Model\ResourceModel\Category\Collection */
        $collection = $this->categoryCollectionFactory->create();

        /* @var $matchingNameCollection \Mageplaza\Blog\Model\ResourceModel\Category\Collection */
        $matchingNameCollection = clone $collection;
        $escapedNamePart = $this->resourceHelper->addLikeEscape(
            $namePart,
            ['position' => 'any']
        );
        $matchingNameCollection->addFieldToFilter(
            'name',
            ['like' => $escapedNamePart]
        )
        ->addFieldToFilter(
            'category_id',
            ['neq' => \Mageplaza\Blog\Model\Category::TREE_ROOT_ID]
        );

        $shownCategoriesIds = [];
        foreach ($matchingNameCollection as $category) {
            /** @var \Mageplaza\Blog\Model\Category $category */
            foreach (explode('/', $category->getPath()) as $parentId) {
                $shownCategoriesIds[$parentId] = 1;
            }
        }
        $collection->addFieldToFilter(
            'category_id',
            ['in' => array_keys($shownCategoriesIds)]
        );

        $categoriesById = [
            \Mageplaza\Blog\Model\Category::TREE_ROOT_ID => [
                'id' => \Mageplaza\Blog\Model\Category::TREE_ROOT_ID,
                'children' => [],
            ],
        ];
        foreach ($collection as $category) {
            /** @var \Mageplaza\Blog\Model\Category $category */
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoriesById[$categoryId])) {
                    $categoriesById[$categoryId] = ['id' => $categoryId, 'children' => []];
                }
            }
            $categoriesById[$category->getId()]['is_active'] = true;
            $categoriesById[$category->getId()]['label'] = $category->getName();
            $categoriesById[$category->getParentId()]['children'][] = & $categoriesById[$category->getId()];
        }
        return $this->jsonEncoder->encode($categoriesById[\Mageplaza\Blog\Model\Category::TREE_ROOT_ID]['children']);
    }

    /**
     * @return string
     */
    public function getAddRootButtonHtml()
    {
        return $this->getChildHtml('add_root_button');
    }

    /**
     * @return string
     */
    public function getAddSubButtonHtml()
    {
        return $this->getChildHtml('add_sub_button');
    }

    /**
     * @return string
     */
    public function getExpandButtonHtml()
    {
        return $this->getChildHtml('expand_button');
    }

    /**
     * @return string
     */
    public function getCollapseButtonHtml()
    {
        return $this->getChildHtml('collapse_button');
    }

    /**
     * @param bool|null $expanded
     * @return string
     */
    public function getLoadTreeUrl($expanded = null)
    {
        $params = ['_current' => true, 'id' => null, 'store' => null];
        if (($expanded === null) && $this->backendSession->getMageplazaBlogCategoryIsTreeWasExpanded()
            || $expanded == true) {
            $params['expand_all'] = true;
        }
        return $this->getUrl('*/*/categoriesJson', $params);
    }

    /**
     * @return string
     */
    public function getNodesUrl()
    {
        return $this->getUrl('mageplaza_blog/category/jsonTree');
    }

    /**
     * @return string
     */
    public function getSwitchTreeUrl()
    {
        return $this->getUrl(
            'mageplaza_blog/category/tree',
            ['_current' => true, 'store' => null, '_query' => false, 'id' => null, 'parent' => null]
        );
    }

    /**
     * @return bool
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsWasExpanded()
    {
        return $this->backendSession->getMageplazaBlogCategoryIsTreeWasExpanded();
    }

    /**
     * @return string
     */
    public function getMoveUrl()
    {
        return $this->getUrl('mageplaza_blog/category/move');
    }

    /**
     * @param null $parentNodeCategory
     * @return array
     */
    public function getTree($parentNodeCategory = null)
    {
        $rootArray = $this->getNodeJson($this->getRoot($parentNodeCategory));
        $tree = isset($rootArray['children']) ? $rootArray['children'] : [];
        return $tree;
    }

    /**
     * @param mixed $parentNodeCategory
     * @return string
     */
    public function getTreeJson($parentNodeCategory = null)
    {
        $rootArray = $this->getNodeJson($this->getRoot($parentNodeCategory));
        $json = $this->jsonEncoder->encode(isset($rootArray['children']) ? $rootArray['children'] : []);
        return $json;
    }

    /**
     * Get JSON of array of Categories, that are breadcrumbs for specified Blog Category path
     *
     * @param string $path
     * @param string $javascriptVarName
     * @return string
     */
    public function getBreadcrumbsJavascript($path, $javascriptVarName)
    {
        if (empty($path)) {
            return '';
        }

        $categories = $this->categoryTree->loadBreadcrumbsArray($path);
        if (empty($categories)) {
            return '';
        }
        foreach ($categories as $key => $category) {
            $categories[$key] = $this->getNodeJson($categories);
        }
        return '<script>require(["prototype"], function(){' . $javascriptVarName . ' = ' . $this->jsonEncoder->encode(
            $categories
        ) .
            ';' .
            ($this->canAddChildCategories()
				? '$("add_child_category_button").show();' : '$("add_child_category_button").hide();') .
            '});</script>';
    }

    /**
     * Get JSON of a tree node or an associative array
     *
     * @param Node|array $node
     * @param int $level
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
        	$node = new \Magento\Framework\Data\Tree\Node($node, 'category_id', new \Magento\Framework\Data\Tree());
        }

        $item = [];
        $item['text'] = $this->buildNodeName($node);

        $item['id'] = $node->getId();
        $item['path'] = $node->getData('path');

        $item['cls'] = 'folder ' . 'active-category';
        $allowMove = $this->isCategoryMoveable($node);
        $item['allowDrop'] = $allowMove;
        $item['allowDrag'] = $allowMove && ($node->getLevel() == 0 ? false : true);

        if ((int)$node->getChildrenCount() > 0) {
            $item['children'] = [];
        }

        $isParent = $this->isParentSelectedCategory($node);

        if ($node->hasChildren()) {
            $item['children'] = [];
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->getNodeJson($child, $level + 1);
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        return $item;
    }

    /**
     * Get Blog Category Name
     *
     * @param \Magento\Framework\DataObject $node
     * @return string
     */
    public function buildNodeName($node)
    {
        $result = $this->escapeHtml($node->getName());
        return $result;
    }

    /**
     * @param Node|array $node
     * @return bool
     */
    public function isCategoryMoveable($node)
    {
        $options = new \Magento\Framework\DataObject(['is_moveable' => true, 'category' => $node]);
        $this->_eventManager->dispatch('adminhtml_mageplaza_blog_category_tree_is_moveable', ['options' => $options]);
        return $options->getIsMoveable();
    }

    /**
     * @param \Magento\Framework\Data\Tree\Node $node
     * @return bool
     */
    protected function isParentSelectedCategory($node)
    {
        if ($node && $this->getCategory()) {
            $pathIds = $this->getCategory()->getPathIds();
            if (in_array($node->getId(), $pathIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if page loaded by outside link to Blog Category edit
     *
     * @return boolean
     */
    public function isClearEdit()
    {
        return (bool)$this->getRequest()->getParam('clear');
    }

    /**
     * Check availability of adding root Blog Category
     *
     * @return boolean
     */
    public function canAddRootCategory()
    {
        $options = new \Magento\Framework\DataObject(['is_allow' => true]);
        $this->_eventManager->dispatch(
            'adminhtml_mageplaza_blog_category_tree_can_add_root_category',
            ['category' => $this->getCategory(), 'options' => $options]
        );

        return $options->getIsAllow();
    }

    /**
     * Check availability of adding child Blog Category
     *
     * @return boolean
     */
    public function canAddChildCategory()
    {
        $options = new \Magento\Framework\DataObject(['is_allow' => true]);
        $this->_eventManager->dispatch(
            'adminhtml_mageplaza_blog_category_tree_can_add_child_category',
            ['category' => $this->getCategory(), 'options' => $options]
        );

        return $options->getIsAllow();
    }
}
