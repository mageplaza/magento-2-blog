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
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory;
use Mageplaza\Blog\Model\TagFactory;

/**
 * Class Tag
 * @package Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab
 */
class Tag extends Extended implements TabInterface
{
    /**
     * @var \Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory
     */
    public $tagCollectionFactory;

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
    public $tagFactory;

    /**
     * Tag constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory $tagCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Mageplaza\Blog\Model\TagFactory $tagFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CollectionFactory $tagCollectionFactory,
        Registry $coreRegistry,
        TagFactory $tagFactory,
        array $data = []
    )
    {
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->coreRegistry         = $coreRegistry;
        $this->tagFactory           = $tagFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('tag_grid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);

        if ($this->getPost()->getId()) {
            $this->setDefaultFilter(['in_tags' => 1]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Tag\Collection $collection */
        $collection = $this->tagCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['related' => $collection->getTable('mageplaza_blog_post_tag')],
            'related.tag_id=main_table.tag_id AND related.post_id=' . ($this->getPost()->getId() ?: 0),
            ['position']
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_tags', [
                'header_css_class' => 'a-center',
                'type'             => 'checkbox',
                'name'             => 'in_tag',
                'values'           => $this->_getSelectedTags(),
                'align'            => 'center',
                'index'            => 'tag_id'
            ]
        );

        $this->addColumn('tag_id', [
                'header'           => __('ID'),
                'sortable'         => true,
                'index'            => 'tag_id',
                'type'             => 'number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn('title', [
                'header'           => __('Name'),
                'index'            => 'name',
                'header_css_class' => 'col-name',
                'column_css_class' => 'col-name'
            ]
        );

        $this->addColumn('position', [
                'header'         => __('Position'),
                'name'           => 'position',
                'width'          => 60,
                'type'           => 'number',
                'validate_class' => 'validate-number',
                'index'          => 'position',
                'editable'       => true,
            ]
        );

        return $this;
    }

    /**
     * Retrieve selected Tags
     * @return array
     */
    protected function _getSelectedTags()
    {
        $tags = $this->getRequest()->getPost('post_tags', null);
        if (!is_array($tags)) {
            $tags = $this->getPost()->getTagsPosition();

            return array_keys($tags);
        }

        return $tags;
    }

    /**
     * Retrieve selected Tags
     * @return array
     */
    public function getSelectedTags()
    {
        $selected = $this->getPost()->getTagsPosition();
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
        return $this->getUrl('*/*/tagsGrid', ['post_id' => $this->getPost()->getId()]);
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
        if ($column->getId() == 'in_tags') {
            $tagIds = $this->_getSelectedTags();
            if (empty($tagIds)) {
                $tagIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.tag_id', ['in' => $tagIds]);
            } else {
                if ($tagIds) {
                    $this->getCollection()->addFieldToFilter('main_table.tag_id', ['nin' => $tagIds]);
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
        return __('Tags');
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
        return $this->getUrl('mageplaza_blog/post/tags', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
