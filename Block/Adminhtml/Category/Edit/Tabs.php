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

namespace Mageplaza\Blog\Block\Adminhtml\Category\Edit;

use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\Category;

/**
 * Class Tabs
 * @package Mageplaza\Blog\Block\Adminhtml\Category\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * Tabs constructor.
     *
     * @param Registry $coreRegistry
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        array $data = []
    ) {
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
     * @return Category
     */
    public function getCategory()
    {
        return $this->coreRegistry->registry('category');
    }

    /**
     * @inheritdoc
     * @throws Exception
     */
    protected function _prepareLayout()
    {
        $this->addTab('category', [
            'label' => __('Category information'),
            'content' => $this->getLayout()
                ->createBlock(
                    Tab\Category::class,
                    'mageplaza_blog_category_edit_tab_category'
                )
                ->toHtml()
        ]);
        $this->addTab('post', [
            'label' => __('Posts'),
            'content' => $this->getLayout()
                ->createBlock(
                    Tab\Post::class,
                    'mageplaza_blog_category_edit_tab_post'
                )
                ->toHtml()
        ]);

        // dispatch event add custom tabs
        $this->_eventManager->dispatch('adminhtml_mageplaza_blog_category_tabs', ['tabs' => $this]);

        return parent::_prepareLayout();
    }
}
