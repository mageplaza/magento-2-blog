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
namespace Mageplaza\Blog\Controller\Post;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Helper\Data as HelperBlog;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Session;

/**
 * Class Index
 * @package Mageplaza\Blog\Controller\Post
 */
class Index extends Action
{
	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
    public $resultPageFactory;
	/**
	 * @var \Mageplaza\Blog\Helper\Data
	 */
    public $helperBlog;
	/**
	 * @var \Magento\Customer\Api\AccountManagementInterface
	 */
    public $accountManagement;
	/**
	 * @var \Magento\Customer\Model\Url
	 */
    public $customerUrl;
	/**
	 * @var \Magento\Customer\Model\Session
	 */
    public $session;
	/**
	 * @var \Magento\Store\Model\StoreManagerInterface
	 */
    public $storeManager;

	/**
	 * Index constructor.
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Mageplaza\Blog\Helper\Data $helperBlog
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
	 * @param \Magento\Customer\Model\Url $customerUrl
	 * @param \Magento\Customer\Model\Session $customerSession
	 */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        HelperBlog $helperBlog,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        Session $customerSession
    ) {
        parent::__construct($context);
        $this->storeManager      = $storeManager;
        $this->helperBlog      = $helperBlog;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        $this->customerUrl       = $customerUrl;
        $this->session           = $customerSession;
    }

	/**
	 * @return \Magento\Framework\View\Result\Page
	 */
    public function execute()
    {
        return $this->resultPageFactory->create();
    }
}
