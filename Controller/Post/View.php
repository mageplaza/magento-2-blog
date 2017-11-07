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

namespace Mageplaza\Blog\Controller\Post;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Helper\Data as HelperBlog;
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
    public $trafficFactory;
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
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;
    /**
     * @var \Mageplaza\Blog\Model\CommentFactory
     */
    public $cmtFactory;
    /**
     * @var \Mageplaza\Blog\Model\LikeFactory
     */
    public $likeFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public $timeZone;

    /**
     * @type \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * View constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Mageplaza\Blog\Model\CommentFactory $commentFactory
     * @param \Mageplaza\Blog\Model\LikeFactory $likeFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mageplaza\Blog\Helper\Data $helperBlog
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Mageplaza\Blog\Model\TrafficFactory $trafficFactory
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Mageplaza\Blog\Model\CommentFactory $commentFactory,
        \Mageplaza\Blog\Model\LikeFactory $likeFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        Context $context,
        ForwardFactory $resultForwardFactory,
        StoreManagerInterface $storeManager,
        HelperBlog $helperBlog,
        PageFactory $resultPageFactory,
        AccountManagementInterface $accountManagement,
        CustomerUrl $customerUrl,
        Session $customerSession,
        TrafficFactory $trafficFactory
    )
    {

        parent::__construct($context);
        $this->storeManager         = $storeManager;
        $this->helperBlog           = $helperBlog;
        $this->resultPageFactory    = $resultPageFactory;
        $this->accountManagement    = $accountManagement;
        $this->customerUrl          = $customerUrl;
        $this->session              = $customerSession;
        $this->timeZone             = $timezone;
        $this->trafficFactory       = $trafficFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->jsonHelper           = $jsonHelper;
        $this->cmtFactory           = $commentFactory;
        $this->likeFactory          = $likeFactory;
        $this->dateTime             = $dateTime;
    }

    /**
     * @return $this|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            $trafficModel = $this->trafficFactory->create()->load($id, 'post_id');
            if ($trafficModel->getId()) {
                $trafficModel->setNumbersView($trafficModel->getNumbersView() + 1);
                $trafficModel->save();
            } else {
                $traffic = $this->trafficFactory->create();
                $traffic->addData(['post_id' => $id, 'numbers_view' => 1])->save();
            }
        }

        if ($this->getRequest()->isAjax() && $this->session->isLoggedIn()) {
            $params       = $this->getRequest()->getParams();
            $customerData = $this->session->getCustomerData();
            $result       = [];
            $now          = getdate();
            if (isset($params['cmt_text'])) {
                $cmtText     = $params['cmt_text'];
                $isReply     = isset($params['isReply']) ? $params['isReply'] : 0;
                $replyId     = isset($params['replyId']) ? $params['replyId'] : 0;
                $commentData = [
                    'post_id'    => $id, '',
                    'entity_id'  => $customerData->getId(),
                    'is_reply'   => $isReply,
                    'reply_id'   => $replyId,
                    'content'    => $cmtText,
                    'created_at' => $this->dateTime->date('M d Y') . ' at ' . $now["hours"] . ":" . $now["minutes"]
                ];

                $commentModel = $this->cmtFactory->create();
                $result       = $this->commentActions(self::COMMENT, $customerData, $commentData, $commentModel);
            }

            if (isset($params['cmtId'])) {
                $cmtId    = $params['cmtId'];
                $likeData = [
                    'comment_id' => $cmtId,
                    'entity_id'  => $customerData->getId()
                ];

                $likeModel = $this->likeFactory->create();
                $result    = $this->commentActions(self::LIKE, $customerData, $likeData, $likeModel, $cmtId);
            }

            return $this->getResponse()->representJson($this->jsonHelper->jsonEncode($result));
        }

        return ($id) ? $this->resultPageFactory->create() : $this->resultForwardFactory->create()->forward('noroute');
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

                    $lastCmt   = $model->getCollection()->setOrder('comment_id', 'desc')->getFirstItem();
                    $lastCmtId = $lastCmt !== null ? $lastCmt->getId() : 1;
                    $result    = [
                        'cmt_id'     => $lastCmtId,
                        'cmt_text'   => $data['content'],
                        'user_cmt'   => $user->getFirstname() . ' ' . $user->getLastname(),
                        'is_reply'   => $data['is_reply'],
                        'reply_cmt'  => $data['reply_id'],
                        'created_at' => __('Just now'),
                        'status'     => 'ok'
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
