<?php

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
use Symfony\Component\Config\Definition\Exception\Exception;

class View extends Action
{
    protected $trafficFactory;
    protected $resultPageFactory;
    protected $helperBlog;
    protected $accountManagement;
    protected $customerUrl;
    protected $session;
    protected $storeManager;

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
        $id=$this->getRequest()->getParams();
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
