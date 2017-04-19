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
use Mageplaza\Blog\Model\TrafficFactory;

class View extends Action
{
	public $trafficFactory;
	public $resultPageFactory;
	public $helperBlog;
	public $accountManagement;
	public $customerUrl;
	public $session;
	public $storeManager;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        HelperBlog $helperBlog,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        Session $customerSession,
        TrafficFactory $trafficFactory
    ) {
    
        parent::__construct($context);
        $this->storeManager      = $storeManager;
        $this->helperBlog        = $helperBlog;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        $this->customerUrl       = $customerUrl;
        $this->session           = $customerSession;
        $this->trafficFactory    = $trafficFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $trafficModel=$this->trafficFactory->create()->load($id, 'post_id');
            if ($trafficModel->getId()) {
                $trafficModel->setNumbersView($trafficModel->getNumbersView()+1);
                $trafficModel->save();
            }
        }

        return $this->resultPageFactory->create();
    }
}
