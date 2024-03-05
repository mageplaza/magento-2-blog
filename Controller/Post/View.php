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

use Exception;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Data\Customer;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Json\Helper\Data as JsonData;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Data as HelperBlog;
use Mageplaza\Blog\Model\Comment;
use Mageplaza\Blog\Model\CommentFactory;
use Mageplaza\Blog\Model\Config\Source\Comments\Status;
use Mageplaza\Blog\Model\Like;
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
    const LIKE    = 2;

    /**
     * @var TrafficFactory
     */
    protected $trafficFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var HelperBlog
     */
    protected $helperBlog;

    /**
     * @var AccountManagementInterface
     */
    protected $accountManagement;

    /**
     * @var CustomerUrl
     */
    protected $customerUrl;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var StoreManagerInterface
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
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * View constructor.
     *
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
    ) {
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
        $this->postFactory          = $postFactory;

        parent::__construct($context);
    }

    /**
     * @return $this|ResponseInterface|ResultInterface|Page
     * @throws Exception
     */
    public function execute()
    {
        $id   = $this->getRequest()->getParam('id');
        $post = $this->helperBlog->getFactoryByType(Data::TYPE_POST)->create()->load($id);
        $this->helperBlog->setCustomerContextId();

        $page       = $this->resultPageFactory->create();
        $pageLayout = ($post->getLayout() === 'empty') ? $this->helperBlog->getSidebarLayout() : $post->getLayout();
        $page->getConfig()->setPageLayout($pageLayout);

        if ($post->getEnabled() !== '1' || !$this->helperBlog->checkStore($post)) {
            return $this->_redirect('noroute');
        }

        $trafficModel = $this->trafficFactory->create()->load($id, 'post_id');
        if ($trafficModel->getId()) {
            $trafficModel->setNumbersView($trafficModel->getNumbersView() + 1);
            $trafficModel->save();
        } else {
            $traffic = $this->trafficFactory->create();
            $traffic->addData(['post_id' => $id, 'numbers_view' => 1])->save();
        }

        if ($this->getRequest()->isAjax()) {
            $params       = $this->getRequest()->getParams();
            $customerData = $this->session->getCustomerData();
            $result       = [];
            if (isset($params['cmt_text'])) {
                $cmt_text   = $params['cmt_text'];
                $content    = htmlentities($cmt_text, ENT_COMPAT, 'UTF-8');
                $htmlEntity = htmlentities($content, ENT_COMPAT, 'UTF-8');
                $content    = html_entity_decode($htmlEntity);

                $cmtText = $content;
                $isReply = isset($params['isReply']) ? $params['isReply'] : 0;
                $replyId = isset($params['replyId']) ? $params['replyId'] : 0;

                $userName    = $this->session->isLoggedIn()
                    ? $customerData->getFirstname() . ' ' . $customerData->getLastname()
                    : $params['guestName'] . ' (Guest)';
                $commentData = [
                    'post_id'    => $id,
                    '',
                    'entity_id'  => $this->session->isLoggedIn() ? $customerData->getId() : 0,
                    'is_reply'   => $isReply,
                    'reply_id'   => $replyId,
                    'content'    => $cmtText,
                    'user_name'  => $userName,
                    'user_email' => $this->session->isLoggedIn() ? $customerData->getEmail() : $params['guestEmail'],
                    'created_at' => $this->dateTime->date(),
                    'status'     => $this->helperBlog->getBlogConfig('comment/need_approve')
                        ? Status::PENDING : Status::APPROVED,
                    'store_ids'  => $this->storeManager->getStore()->getId()
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

        return $this->resultPageFactory->create();
    }

    /**
     * @param int $action
     * @param CustomerInterface|Customer|null $user
     * @param array $data
     * @param Like|Comment $model
     * @param null $cmtId
     *
     * @return array
     */
    public function commentActions($action, $user, $data, $model, $cmtId = null)
    {
        try {
            switch ($action) {
                /** Comment action */
                case self::COMMENT:
                    $model->addData($data)->save();
                    $cmtHasReply = $model->getCollection()
                        ->addFieldToFilter('comment_id', $data['reply_id'])
                        ->getFirstItem();
                    if ($cmtHasReply->getId()) {
                        $cmtHasReply->setHasReply(1)->save();
                    }
                    $this->_eventManager->dispatch('blog_post_comment', ['comment_data' => $data]);

                    $lastCmt   = $model->getCollection()->setOrder('comment_id', 'desc')->getFirstItem();
                    $lastCmtId = $lastCmt !== null ? $lastCmt->getId() : 1;
                    $users     = $user ? $user->getFirstname() . ' ' . $user->getLastname() : $data['user_name'];

                    $result = [
                        'cmt_id'     => $lastCmtId,
                        'cmt_text'   => $data['content'],
                        'user_cmt'   => $users,
                        'is_reply'   => $data['is_reply'],
                        'reply_cmt'  => $data['reply_id'],
                        'created_at' => __('Just now'),
                        'status'     => $data['status']
                    ];
                    break;
                /** Like action */
                case self::LIKE:
                    $checkLike = $this->isLikedComment($cmtId, $user->getId(), $model);
                    if (!$checkLike) {
                        $model->addData($data)->save();
                    }
                    $likes      = $model->getCollection()->addFieldToFilter('comment_id', $cmtId);
                    $countLikes = $likes->getSize() ?: '';
                    $isLiked    = $checkLike ? 'yes' : 'no';
                    $result     = [
                        'liked'      => $isLiked,
                        'comment_id' => $cmtId,
                        'count_like' => $countLikes,
                        'status'     => 'ok'
                    ];
                    break;
                default:
                    $result = ['status' => 'error', 'error' => __('Action not found.')];
                    break;
            }
        } catch (Exception $e) {
            $result = ['status' => 'error', 'error' => $e->getMessage()];
        }

        return $result;
    }

    /**
     * Check if user like a comment
     *
     * @param int $cmtId
     * @param int $userId
     * @param Like $model
     *
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
                } catch (Exception $e) {
                    return false;
                }
            }
        }

        return false;
    }
}
