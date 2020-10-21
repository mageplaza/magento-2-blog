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

namespace Mageplaza\Blog\Controller\SiteMap;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Index
 * @package Mageplaza\Blog\Controller\SiteMap
 */
class Index extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var Data
     */
    protected $_helperBlog;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helperData
    ) {
        $this->_helperBlog = $helperData;
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context);
    }

    /**
     * @return Page
     */
    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $page->getConfig()->setPageLayout($this->_helperBlog->getSidebarLayout());
        $page->getConfig()->getTitle()->set('Mageplaza Site map');

        return $page;
    }
}
