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

namespace Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data;
use Mageplaza\Blog\Model\ResourceModel\PostHistory\Collection;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\Tag;

/**
 * Class Product
 * @package Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab
 */
class History extends Extended implements TabInterface
{

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var Collection
     */
    protected $historyCollection;

    /**
     * Product constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Data $backendHelper
     * @param Collection $historyCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Data $backendHelper,
        Collection $historyCollection,
        array $data = []
    ) {
        $this->coreRegistry = $coreRegistry;
        $this->historyCollection = $historyCollection;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     */
    public function _construct()
    {
        parent::_construct();

        $this->setId('history_id');
        $this->setDefaultSort('history_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        /** @var Collection $collection */
        $collection = $this->historyCollection;
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('name', [
            'header'           => __('Name'),
            'sortable'         => true,
            'index'            => 'name',
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name'
        ]);
        $this->addColumn('short_description', [
            'header'           => __('Short Description'),
            'index'            => 'short_description',
            'header_css_class' => 'col-short-description',
            'column_css_class' => 'col-short-description'
        ]);
        $this->addColumn('store_ids', [
            'header'           => __('Store View'),
            'index'            => 'store_ids',
            'header_css_class' => 'col-store-ids',
            'column_css_class' => 'col-store-ids'
        ]);
        $this->addColumn('category_ids', [
            'header'           => __('Categories'),
            'index'            => 'category_ids',
            'header_css_class' => 'col-category-ids',
            'column_css_class' => 'col-category-ids'
        ]);
        $this->addColumn('topic_ids', [
            'header'           => __('Topics'),
            'index'            => 'topic_ids',
            'header_css_class' => 'col-topic-ids',
            'column_css_class' => 'col-topic-ids'
        ]);
        $this->addColumn('tag_ids', [
            'header'           => __('Tags'),
            'index'            => 'tag_ids',
            'header_css_class' => 'col-tag-ids',
            'column_css_class' => 'col-tag-ids'
        ]);
        $this->addColumn('modifier_id', [
            'header'           => __('Modified by'),
            'index'            => 'modifier_id',
            'header_css_class' => 'col-modifier-id',
            'column_css_class' => 'col-modifier-id'
        ]);
        $this->addColumn('updated_at', [
            'header'           => __('Modified At'),
            'index'            => 'updated_at',
            'header_css_class' => 'col-updated-at',
            'column_css_class' => 'col-updated-at'
        ]);

        return $this;
    }

    /**
     * @param Tag|Object $item
     *
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
        return $this->getUrl('*/*/historyGrid', ['post_id' => $this->getPost()->getId()]);
    }

    /**
     * @return \Mageplaza\Blog\Model\Post
     */
    public function getPost()
    {
        return $this->coreRegistry->registry('mageplaza_blog_post');
    }

    /**
     * @return string
     */
    public function getTabLabel()
    {
        return __('Edit History');
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
        return $this->getUrl('mageplaza_blog/post/history', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
