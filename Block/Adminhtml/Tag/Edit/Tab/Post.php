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

namespace Mageplaza\Blog\Block\Adminhtml\Tag\Edit\Tab;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Helper\Data;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;
use Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory;

/**
 * Class Post
 * @package Mageplaza\Blog\Block\Adminhtml\Tag\Edit\Tab
 */
class Post extends Extended implements TabInterface
{
    /**
     * Post collection factory
     *
     * @var CollectionFactory
     */
    public $postCollectionFactory;

    /**
     * Registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * Post factory
     *
     * @var PostFactory
     */
    public $postFactory;

    /**
     * Post constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Data $backendHelper
     * @param PostFactory $postFactory
     * @param CollectionFactory $postCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        Data $backendHelper,
        PostFactory $postFactory,
        CollectionFactory $postCollectionFactory,
        array $data = []
    ) {
        $this->coreRegistry          = $coreRegistry;
        $this->postFactory           = $postFactory;
        $this->postCollectionFactory = $postCollectionFactory;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('post_grid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(false);
        $this->setUseAjax(true);

        if ($this->getTag()->getId()) {
            $this->setDefaultFilter(['in_posts' => 1]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function _prepareCollection()
    {
        /** @var Collection $collection */
        $collection = $this->postCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['related' => $collection->getTable('mageplaza_blog_post_tag')],
            'related.post_id=main_table.post_id AND related.tag_id=' . (int) $this->getRequest()->getParam('id', 0),
            ['position']
        );

        $collection->addFilterToMap('post_id', 'main_table.post_id');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('in_posts', [
            'header_css_class' => 'a-center',
            'type'             => 'checkbox',
            'name'             => 'in_post',
            'values'           => $this->_getSelectedPosts(),
            'align'            => 'center',
            'index'            => 'post_id'
        ]);
        $this->addColumn('post_id', [
            'header'           => __('ID'),
            'sortable'         => true,
            'index'            => 'post_id',
            'type'             => 'number',
            'header_css_class' => 'col-id',
            'column_css_class' => 'col-id'
        ]);
        $this->addColumn('title', [
            'header'           => __('Name'),
            'index'            => 'name',
            'header_css_class' => 'col-name',
            'column_css_class' => 'col-name'
        ]);
        $this->addColumn('position', [
            'header'         => __('Position'),
            'name'           => 'position',
            'width'          => 60,
            'type'           => 'number',
            'validate_class' => 'validate-number',
            'index'          => 'position',
            'editable'       => true
        ]);

        return $this;
    }

    /**
     * Retrieve selected Posts
     * @return array
     */
    protected function _getSelectedPosts()
    {
        $posts = $this->getRequest()->getPost('tag_posts', null);
        if (!is_array($posts)) {
            $posts = $this->getTag()->getPostsPosition();

            return array_keys($posts);
        }

        return $posts;
    }

    /**
     * Retrieve selected Posts
     * @return array
     */
    public function getSelectedPosts()
    {
        $selected = $this->getTag()->getPostsPosition();
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
     * @param \Mageplaza\Blog\Model\Post|Object $item
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
        return $this->getUrl('*/*/postsGrid', ['id' => $this->getTag()->getId()]);
    }

    /**
     * @return \Mageplaza\Blog\Model\Tag
     */
    public function getTag()
    {
        return $this->coreRegistry->registry('mageplaza_blog_tag');
    }

    /**
     * @param Column $column
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'in_posts') {
            $postIds = $this->_getSelectedPosts();
            if (empty($postIds)) {
                $postIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.post_id', ['in' => $postIds]);
            } elseif ($postIds) {
                $this->getCollection()->addFieldToFilter('main_table.post_id', ['nin' => $postIds]);
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
        return __('Posts');
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
        return $this->getUrl('mageplaza_blog/tag/posts', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
