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
	public $jsonHelper;
	public $cmtFactory;
	public $likeFactory;
	public $dateTime;

    public function __construct(
		\Magento\Framework\Json\Helper\Data $jsonHelper,
		\Mageplaza\Blog\Model\CommentFactory $commentFactory,
		\Mageplaza\Blog\Model\LikeFactory $likeFactory,
		\Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
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
        $this->jsonHelper = $jsonHelper;
        $this->cmtFactory = $commentFactory;
        $this->likeFactory = $likeFactory;
        $this->dateTime = $dateTime;
    }

    public function execute()
    {
        $id=$this->getRequest()->getParam('id');
        if ($id) {
            $trafficModel=$this->trafficFactory->create()->load($id, 'post_id');
            if ($trafficModel->getId()) {
                $trafficModel->setNumbersView($trafficModel->getNumbersView()+1);
                $trafficModel->save();
            } else {
            	$traffic = $this->trafficFactory->create();
				$traffic->addData(['post_id' => $id, 'numbers_view' => 1])->save();
			}
        }

        if ($this->getRequest()->isAjax()) {
			$cmtText = $this->getRequest()->getParam('cmt_text');
			$customerData = $this->helperBlog->getCustomerData();
			$commentData = [
				'post_id'	=> $id, '',
				'entity_id'	=> $customerData->getId(),
				'content'	=> $cmtText,
				'created_at'=> $this->dateTime->date()
			];

			$comment = $this->cmtFactory->create();

			try {
				$comment->addData($commentData)->save();
				$lastCmt = $comment->getCollection()->setOrder('comment_id', 'desc')->getFirstItem();
				$lastCmtId = $lastCmt !== null ? $lastCmt->getId() : 1;
				$result = [
					'cmt_id'	=> $lastCmtId,
					'cmt_text' 	=> $cmtText,
					'user_cmt'	=> $customerData->getFirstname() .' '. $customerData->getLastname(),
					'created_at'=> __('Just now'),
					'status' 	=> 'ok'
				];
			} catch (\Exception $e) {
				$result = ['status' => 'error', 'error' => $e->getMessage()];
			}

			return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
		}

        return $this->resultPageFactory->create();
    }
}
