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
namespace Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab;

class Product extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Tag collection factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory
     */
    public $productCollectionFactory;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * Tag factory
     *
     * @var \Mageplaza\Blog\Model\TagFactory
     */
    public $productFactory;

    /**
     * constructor
     *
     * @param \Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Mageplaza\Blog\Model\TagFactory $tagFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->coreRegistry         = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('product_grid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
        if ($this->getPost()->getId()) {
            $this->setDefaultFilter(['in_products' => 1]);
        }
    }

    /**
     * prepare the collection

     * @return $this
     */
    protected function _prepareCollection()
    {

        /** @var \Mageplaza\Blog\Model\ResourceModel\Tag\Collection $collection */
        $collection = $this->productCollectionFactory->create();
        if ($this->getPost()->getId()) {
            $constraint = 'related.post_id='.$this->getPost()->getId();
        } else {
            $constraint = 'related.post_id=0';
        }
//		$collection->getSelect()->joinLeft([
//			'related' => $collection->getTable('mageplaza_blog_post_product'),
//			'main_table' => $collection->getTable('catalog_product_entity')
//		],'related.entity_id = e.entity_id')->where($constraint);
//		$collection->addFieldToFilter('entity_id',['eq'=>800]);
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        return $this;
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            [
                'header_css_class'  => 'a-center',
                'type'   => 'checkbox',
                'name'   => 'in_product',
                'values' => $this->_getSelectedProducts(),
                'align'  => 'center',
                'index'  => 'entity_id'
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'entity_id',
                'type' => 'number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'title',
            [
                'header' => __('Sku'),
                'index' => 'sku',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn(
            'position',
            [
                'header' => __('Position'),
                'name'   => 'position',
                'width'  => 60,
                'type'   => 'number',
                'validate_class' => 'validate-number',
                'index' => 'position',
                'editable'  => true,
            ]
        );
        return $this;
    }

    /**
     * Retrieve selected Tags

     * @return array
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getPostProducts();
        if (!is_array($products)) {
            $products = $this->getPost()->getProductsPosition();
            return array_keys($products);
        }
        return $products;
    }

    /**
     * Retrieve selected Tags

     * @return array
     */
    public function getSelectedProducts()
    {
        $selected = $this->getPost()->getProductsPosition();
        if (!is_array($selected)) {
            $selected = [];
        } else {
            foreach ($selected as $key => $value) {
                $selected[$key] = ['position' => $value];
            }
        }
        return $selected;
    }

    /**
     * @param \Mageplaza\Blog\Model\Tag|\Magento\Framework\Object $item
     * @return string
     */
    public function getRowUrl($item)
    {
        return '#';
    }

    /**
     * get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/productsGrid',
            [
                'post_id' => $this->getPost()->getId()
            ]
        );
    }

    /**
     * @return \Mageplaza\Blog\Model\Post
     */
    public function getPost()
    {
        return $this->coreRegistry->registry('mageplaza_blog_post');
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_products') {
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in'=>$productIds]);
            } else {
                if ($productIds) {
                    $this->getCollection()->addFieldToFilter('entity_id', ['nin'=>$productIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Products');
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getTabUrl()
    {
        return $this->getUrl('mageplaza_blog/post/products', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
