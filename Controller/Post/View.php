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
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Controller\Post;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Json\Helper\Data as JsonData;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Data as HelperBlog;
use Mageplaza\Blog\Model\CommentFactory;
use Mageplaza\Blog\Model\Config\Source\Comments\Status;
use Mageplaza\Blog\Model\LikeFactory;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\TrafficFactory;

/**
 * Class View
 * @package Mageplaza\Blog\Controller\Post
 */
class View extends Action
{
    const COMMENT = 1;
    const LIKE = 2;

    /**
     * @var \Mageplaza\Blog\Model\TrafficFactory
     */
    protected $trafficFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    protected $helperBlog;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var JsonData
     */
    protected $jsonHelper;

    /**
     * @var CommentFactory
     */
    protected $cmtFactory;

    /**
     * @var LikeFactory
     */
    protected $likeFactory;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var TimezoneInterface
     */
    protected $timeZone;

    /**
     * @type \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Mageplaza\Blog\Model\PostFactory
     */
    protected $postFactory;

    /**
     * View constructor.
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param StoreManagerInterface $storeManager
     * @param JsonData $jsonHelper
     * @param CommentFactory $commentFactory
     * @param LikeFactory $likeFactory
     * @param DateTime $dateTime
     * @param TimezoneInterface $timezone
     * @param HelperBlog $helperBlog
     * @param PageFactory $resultPageFactory
     * @param AccountManagementInterface $accountManagement
     * @param CustomerUrl $customerUrl
     * @param Session $customerSession
     * @param TrafficFactory $trafficFactory
     * @param PostFactory $postFactory
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        StoreManagerInterface $storeManager,
        JsonData $jsonHelper,
        CommentFactory $commentFactory,
        LikeFactory $likeFactory,
        DateTime $dateTime,
        TimezoneInterface $timezone,
        HelperBlog $helperBlog,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        Session $customerSession,
        TrafficFactory $trafficFactory,
        PostFactory $postFactory
    )
    {
        parent::__construct($context);

        $this->storeManager = $storeManager;
        $this->helperBlog = $helperBlog;
        $this->resultPageFactory = $resultPageFactory;
        $this->accountManagement = $accountManagement;
        $this->customerUrl = $customerUrl;
        $this->session = $customerSession;
        $this->timeZone = $timezone;
        $this->trafficFactory = $trafficFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->jsonHelper = $jsonHelper;
        $this->cmtFactory = $commentFactory;
        $this->likeFactory = $likeFactory;
        $this->dateTime = $dateTime;
        $this->postFactory = $postFactory;
    }

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     * @throws \Exception
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $post = $this->helperBlog->getFactoryByType(Data::TYPE_POST)->create()->load($id);
        if (!$post->getEnabled()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }

        $trafficModel = $this->trafficFactory->create()->load($id, 'post_id');
        if ($trafficModel->getId()) {
            $trafficModel->setNumbersView($trafficModel->getNumbersView() + 1);
            $trafficModel->save();
        } else {
            $traffic = $this->trafficFactory->create();
            $traffic->addData(['post_id' => $id, 'numbers_view' => 1])->save();
        }

        if ($this->getRequest()->isAjax() && $this->session->isLoggedIn()) {
            $params = $this->getRequest()->getParams();
            $customerData = $this->session->getCustomerData();
            $result = [];
            if (isset($params['cmt_text'])) {
                $cmtText = $params['cmt_text'];
                $isReply = isset($params['isReply']) ? $params['isReply'] : 0;
                $replyId = isset($params['replyId']) ? $params['replyId'] : 0;
                $commentData = [
                    'post_id' => $id, '',
                    'entity_id' => $customerData->getId(),
                    'is_reply' => $isReply,
                    'reply_id' => $replyId,
                    'content' => $cmtText,
                    'created_at' => $this->dateTime->date(),
                    'status' => $this->helperBlog->getBlogConfig('comment/need_approve') ? Status::PENDING : Status::APPROVED,
                    'store_ids' => $this->storeManager->getStore()->getId()
                ];

                $commentModel = $this->cmtFactory->create();
                $result = $this->commentActions(self::COMMENT, $customerData, $commentData, $commentModel);
            }

            if (isset($params['cmtId'])) {
                $cmtId = $params['cmtId'];
                $likeData = [
                    'comment_id' => $cmtId,
                    'entity_id' => $customerData->getId()
                ];

                $likeModel = $this->likeFactory->create();
                $result = $this->commentActions(self::LIKE, $customerData, $likeData, $likeModel, $cmtId);
            }

            return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
        }

        return $this->resultPageFactory->create();
    }

    /**
     * @param $action
     * @param $user
     * @param $data
     * @param $model
     * @param null $cmtId
     * @return array
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
                        'cmt_id' => $lastCmtId,
                        'cmt_text' => $data['content'],
                        'user_cmt' => $user->getFirstname() . ' ' . $user->getLastname(),
                        'is_reply' => $data['is_reply'],
                        'reply_cmt' => $data['reply_id'],
                        'created_at' => __('Just now'),
                        'status' => $data['status']
                    ];
                    break;
                //like action
                case self::LIKE:
                    $checkLike = $this->isLikedComment($cmtId, $user->getId(), $model);
                    if (!$checkLike) {
                        $model->addData($data)->save();
                    }
                    $likes = $model->getCollection()->addFieldToFilter('comment_id', $cmtId);
                    $countLikes = ($likes->getSize()) ? $likes->getSize() : '';
                    $isLiked = ($checkLike) ? "yes" : "no";
                    $result = [
                        'liked' => $isLiked,
                        'comment_id' => $cmtId,
                        'count_like' => $countLikes,
                        'status' => 'ok'
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
     * check if user like a comment
     * @param $cmtId
     * @param $userId
     * @param $model
     * @return bool
     */
    public function isLikedComment($cmtId, $userId, $model)
    {
        $liked = $model->getCollection()->addFieldToFilter('comment_id', $cmtId);
        foreach ($liked as $item) {
            if ($item->getEntityId() == $userId) {
                try {
                    $item->delete();

                    return true;
                } catch (\Exception $e) {
                    return false;
                }
            }
        }

        return false;
    }
}
