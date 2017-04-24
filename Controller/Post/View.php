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
	const COMMENT = 1;
	const LIKE = 2;

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

        if ($this->getRequest()->isAjax() && $this->helperBlog->isLoggedIn()) {
        	$params = $this->getRequest()->getParams();
			$customerData = $this->helperBlog->getCustomerData();
			$result = [];

        	if (isset($params['cmt_text'])) {
				$cmtText = $params['cmt_text'];
				$isReply = isset($params['isReply']) ? $params['isReply'] : 0;
				$replyId = isset($params['replyId']) ? $params['replyId'] : 0;
				$commentData = [
					'post_id'	=> $id, '',
					'entity_id'	=> $customerData->getId(),
					'is_reply'	=> $isReply,
					'reply_id'	=> $replyId,
					'content'	=> $cmtText,
					'created_at'=> $this->dateTime->date('M d Y') .' at '. $this->dateTime->date('H:i')
				];

				$commentModel = $this->cmtFactory->create();
				$result = $this->commentActions(self::COMMENT, $customerData, $commentData, $commentModel);
			}

			if (isset($params['cmtId'])) {
        		$cmtId = $params['cmtId'];
        		$likeData = [
        			'comment_id'	=> $cmtId,
					'entity_id'		=> $customerData->getId()
				];

        		$likeModel = $this->likeFactory->create();
        		$result = $this->commentActions(self::LIKE, $customerData, $likeData, $likeModel, $cmtId);
			}

			return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
		}

        return $this->resultPageFactory->create();
    }

    /**
	 * like comment action
	 */
    public function commentActions($action, $user, $data, $model, $cmtId = null)
	{
		try {
			switch ($action) {
				//comment action
				case self::COMMENT:
					$model->addData($data)->save();
					$cmtHasReply = $model->getCollection()
						->addFieldToFilter('comment_id', $data['reply_id'])
						->getFirstItem();
					if ($cmtHasReply->getId()) {
						$cmtHasReply->setHasReply(1)->save();
					}

					$lastCmt = $model->getCollection()->setOrder('comment_id', 'desc')->getFirstItem();
					$lastCmtId = $lastCmt !== null ? $lastCmt->getId() : 1;
					$result = [
						'cmt_id'	=> $lastCmtId,
						'cmt_text' 	=> $data['content'],
						'user_cmt'	=> $user->getFirstname() .' '. $user->getLastname(),
						'is_reply'	=> $data['is_reply'],
						'reply_cmt'	=> $data['reply_id'],
						'created_at'=> __('Just now'),
						'status' 	=> 'ok'
					];
					break;
				//like action
				case self::LIKE:
					$checkLike = $this->isLikedComment($cmtId, $user->getId(), $model);
					if (!$checkLike) {
						$model->addData($data)->save();
					}
					$likes      = $model->getCollection()->addFieldToFilter('comment_id', $cmtId);
					$countLikes = $likes->getSize();
					$result     = [
						'comment_id' => $cmtId,
						'count_like' => $countLikes,
						'status'     => 'ok'
					];
					break;
				default:
					$result = ['status' => 'error', 'error' => __('Action not found.')];
					break;
			}
		} catch (\Exception $e) {
			$result = ['status' => 'error', 'error' => $e->getMessage()];
		}

		return $result;
	}

	/**
	 * check if user liked a comment
	 * @param $cmtId
	 * @param $userId
	 * @param $model
	 */
	public function isLikedComment($cmtId, $userId, $model)
	{
		$liked = $model->load($cmtId, 'comment_id');
		if ($liked->getEntityId() == $userId) {
			try {
				$liked->delete();
				return true;
			} catch (\Exception $e) {
				return false;
			}
		}

		return false;
	}
}
