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

namespace Mageplaza\Blog\Block\Adminhtml\Category\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;

/**
 * @method Tabs setTitle(\string $title)
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Tabs template
     *
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * Registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * Tabs constructor.
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        array $data = []
    )
    {
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * Initialize Tabs
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('category_info_tabs');
        $this->setDestElementId('category_tab_content');
        $this->setTitle(__('Category Data'));
    }

    /**
     * Retrieve Blog Category object
     *
     * @return \Mageplaza\Blog\Model\Category
     */
    public function getCategory()
    {
        return $this->coreRegistry->registry('category');
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        $this->addTab('category', [
                'label'   => __('Category information'),
                'content' => $this->getLayout()
                    ->createBlock('Mageplaza\Blog\Block\Adminhtml\Category\Edit\Tab\Category', 'mageplaza_blog_category_edit_tab_category')
                    ->toHtml()
            ]
        );

        $this->addTab('post', [
                'label'   => __('Posts'),
                'content' => $this->getLayout()
                    ->createBlock('Mageplaza\Blog\Block\Adminhtml\Category\Edit\Tab\Post', 'mageplaza_blog_category_edit_tab_post')
                    ->toHtml()
            ]
        );

        // dispatch event add custom tabs
        $this->_eventManager->dispatch('adminhtml_mageplaza_blog_category_tabs', ['tabs' => $this]);

        return parent::_prepareLayout();
    }
}
