<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Adminhtml\Author;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\Registry;

/**
 * Class CustomerGrid
 * @package Mageplaza\Blog\Block\Adminhtml\Author
 */
class CustomerGrid extends Extended
{
    /**
     * Core registry
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var CustomerCollectionFactory
     */
    protected $_customerCollectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $customerGroup;

    /**
     * CustomerGrid constructor.
     *
     * @param Context $context
     * @param Data $backendHelper
     * @param CustomerCollectionFactory $customerFactory
     * @param CollectionFactory $customerGroup
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CustomerCollectionFactory $customerFactory,
        CollectionFactory $customerGroup,
        array $data = []
    ) {
        $this->_customerCollectionFactory = $customerFactory;
        $this->customerGroup = $customerGroup;

        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer-grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $collection = $this->_customerCollectionFactory->create();
        $collection->getSelect()->joinLeft(
            ['author' => $collection->getTable('mageplaza_blog_author')],
            'e.entity_id = author.customer_id',
            ['author.user_id AS user_id']
        )->where('author.user_id IS NULL')->getConnection();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('customer_id', [
            'header_css_class' => 'a-center',
            'type' => 'radio',
            'html_name' => 'customer_id',
            'align' => 'center',
            'index' => 'entity_id',
            'filter' => false,
            'sortable' => false
        ]);
        $this->addColumn('entity_id', [
            'header' => __('ID'),
            'sortable' => true,
            'index' => 'entity_id'
        ]);
        $this->addColumn('firstname', [
            'header' => __('First Name'),
            'index' => 'firstname',
            'type' => 'text',
            'sortable' => true,
        ]);
        $this->addColumn('lastname', [
            'header' => __('Last Name'),
            'index' => 'lastname',
            'type' => 'text',
            'sortable' => true,
        ]);
        $this->addColumn('email', [
            'header' => __('Email'),
            'index' => 'email',
            'type' => 'text',
            'sortable' => true,
        ]);
        $this->addColumn('group_id', [
            'header' => __('Group'),
            'index' => 'group_id',
            'type' => 'options',
            'options' => $this->customerGroup->create()->toOptionHash(),
            'sortable' => true,
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mageplaza_blog/author/customergrid', ['_current' => true]);
    }
}
