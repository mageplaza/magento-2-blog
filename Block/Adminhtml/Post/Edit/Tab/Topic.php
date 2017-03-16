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

class Topic extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Topic collection factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory
     */
	public $topicCollectionFactory;

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
	public $coreRegistry;

    /**
     * Topic factory
     *
     * @var \Mageplaza\Blog\Model\TopicFactory
     */
	public $topicFactory;

    /**
     * constructor
     *
     * @param \Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory $topicCollectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Mageplaza\Blog\Model\TopicFactory $topicFactory
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param array $data
     */
    public function __construct(
        \Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory $topicCollectionFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Mageplaza\Blog\Model\TopicFactory $topicFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
    
        $this->topicCollectionFactory = $topicCollectionFactory;
        $this->coreRegistry           = $coreRegistry;
        $this->topicFactory           = $topicFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Set grid params
     */
    public function _construct()
    {
        parent::_construct();
        $this->setId('topic_grid');
        $this->setDefaultSort('position');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
        if ($this->getPost()->getId()) {
            $this->setDefaultFilter(['in_topics'=>1]);
        }
    }

    /**
     * prepare the collection

     * @return $this
     */
    protected function _prepareCollection()
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Topic\Collection $collection */
        $collection = $this->topicCollectionFactory->create();
        if ($this->getPost()->getId()) {
            $constraint = 'related.post_id='.$this->getPost()->getId();
        } else {
            $constraint = 'related.post_id=0';
        }
        $collection->getSelect()->joinLeft(
            ['related' => $collection->getTable('mageplaza_blog_post_topic')],
            'related.topic_id=main_table.topic_id AND '.$constraint,
            ['position']
        );
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
            'in_topics',
            [
                'header_css_class'  => 'a-center',
                'type'   => 'checkbox',
                'name'   => 'in_topic',
                'values' => $this->_getSelectedTopics(),
                'align'  => 'center',
                'index'  => 'topic_id'
            ]
        );
        $this->addColumn(
            'topic_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'topic_id',
                'type' => 'number',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'title',
            [
                'header' => __('Name'),
                'index' => 'name',
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
     * Retrieve selected Topics

     * @return array
     */
    protected function _getSelectedTopics()
    {
        $topics = $this->getPostTopics();
        if (!is_array($topics)) {
            $topics = $this->getPost()->getTopicsPosition();
            return array_keys($topics);
        }
        return $topics;
    }

    /**
     * Retrieve selected Topics

     * @return array
     */
    public function getSelectedTopics()
    {
        $selected = $this->getPost()->getTopicsPosition();
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
     * @param \Mageplaza\Blog\Model\Topic|\Magento\Framework\Object $item
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
            '*/*/topicsGrid',
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
        if ($column->getId() == 'in_topics') {
            $topicIds = $this->_getSelectedTopics();
            if (empty($topicIds)) {
                $topicIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('main_table.topic_id', ['in'=>$topicIds]);
            } else {
                if ($topicIds) {
                    $this->getCollection()->addFieldToFilter('main_table.topic_id', ['nin'=>$topicIds]);
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
        return __('Topics');
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
        return $this->getUrl('mageplaza_blog/post/topics', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax only';
    }
}
