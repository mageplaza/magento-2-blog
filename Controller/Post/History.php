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

namespace Mageplaza\Blog\Controller\Post;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\Config\Source\SideBarLR;
use Mageplaza\Blog\Model\ResourceModel\Author\Collection as AuthorCollection;

/**
 * Class View
 * @package Mageplaza\Blog\Controller\Author
 */
class History extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var Data
     */
    protected $_helperBlog;

    /**
     * @var AuthorCollection
     */
    protected $authorCollection;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param AuthorCollection $authorCollection
     * @param Session $customerSession
     * @param Registry $coreRegistry
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        AuthorCollection $authorCollection,
        Session $customerSession,
        Registry $coreRegistry,
        Data $helperData
    ) {
        $this->_helperBlog = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->authorCollection = $authorCollection;
        $this->customerSession = $customerSession;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->_helperBlog->setCustomerContextId();

        if (!$this->_helperBlog->isEnabledAuthor()) {
            $resultRedirect->setPath('customer/account');

            return $resultRedirect;
        }

        if (!$this->_helperBlog->isAuthor()) {
            $author = $this->_helperBlog->getCurrentAuthor();

            $this->coreRegistry->register('mp_author', $author);

            $page = $this->resultPageFactory->create();
            $page->getConfig()->setPageLayout(SideBarLR::LEFT);
            $page->getConfig()->getTitle()->set($author->getName());

            return $page;
        }

        if ($this->_helperBlog->isLogin()) {
            $resultRedirect->setPath('mpblog/*/signup');
        } else {
            $resultRedirect->setPath('customer/account');
        }

        return $resultRedirect;
    }
}
