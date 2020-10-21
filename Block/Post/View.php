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

namespace Mageplaza\Blog\Block\Post;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Messages;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\Post;
use Mageplaza\Blog\Model\PostLike;

/**
 * Class View
 * @package Mageplaza\Blog\Block\Post
 * @method Post getPost()
 * @method void setPost($post)
 */
class View extends \Mageplaza\Blog\Block\Listpost
{
    /**
     * config logo blog path
     */
    const LOGO = 'mageplaza/blog/logo/';

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $post = $this->postFactory->create();
        $id = $this->getRequest()->getParam('id');
        $historyId = $this->getRequest()->getParam('historyId');

        if ($historyId) {
            $history = $this->helperData->getFactoryByType(Data::TYPE_HISTORY)->create()->load($historyId);
            $post = $this->helperData->getFactoryByType(Data::TYPE_POST)->create()->load($history->getPostId());
            $data = $history->getData();
            $post->addData($data);
        } elseif ($id) {
            $post->load($id);
        }
        $this->setPost($post);
    }

    /**
     * @return bool
     */
    public function getRelatedMode()
    {
        return (int)$this->helperData->getConfigGeneral('related_mode') === 1 ? true : false;
    }

    /**
     * @param $value
     *
     * @return string
     */
    public function getDecrypt($value)
    {
        return $this->enc->decrypt($value);
    }

    /**
     * @return mixed
     */
    protected function getBlogObject()
    {
        return $this->getPost();
    }

    /**
     * check customer is logged in or not
     */
    public function isLoggedIn()
    {
        return $this->helperData->isLogin();
    }

    /**
     * @return string
     */
    public function checkRss()
    {
        return $this->helperData->getBlogUrl('post/rss');
    }

    /**
     * @param $topic
     *
     * @return string
     */
    public function getTopicUrl($topic)
    {
        return $this->helperData->getBlogUrl($topic, Data::TYPE_TOPIC);
    }

    /**
     * @return mixed|string
     */
    public function getPubId()
    {
        return $this->helperData->getBlogConfig('share/pubid_id') === 'ra-5983d393d9a9b2c9' ?
            $this->helperData->getBlogConfig('share/pubid_id') :
            $this->getDecrypt($this->helperData->getBlogConfig('share/pubid_id'));
    }

    /**
     * @param $tag
     *
     * @return string
     */
    public function getTagUrl($tag)
    {
        return $this->helperData->getBlogUrl($tag, Data::TYPE_TAG);
    }

    /**
     * @param $category
     *
     * @return string
     */
    public function getCategoryUrl($category)
    {
        return $this->helperData->getBlogUrl($category, Data::TYPE_CATEGORY);
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    public function helperComment($code)
    {
        return $this->helperData->getBlogConfig('comment/' . $code);
    }

    /**
     * get comments tree html
     *
     * @return mixed
     */
    public function getCommentsHtml()
    {
        return $this->commentTree;
    }

    /**
     * @param $userId
     *
     * @return CustomerInterface
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getUserComment($userId)
    {
        return $this->customerRepository->getById($userId);
    }

    /**
     * @param $cmtId
     *
     * @return int|string
     */
    public function getCommentLikes($cmtId)
    {
        $likes = $this->likeFactory->create()
            ->getCollection()
            ->addFieldToFilter('comment_id', $cmtId)
            ->getSize();

        return $likes ?: '';
    }

    /**
     * @param $cmtId
     *
     * @return bool
     */
    public function isLiked($cmtId)
    {
        if ($this->helperData->isLogin()) {
            $customerId = $this->helperData->getCustomerIdByContext();
            $likes = $this->likeFactory->create()->getCollection();
            foreach ($likes as $like) {
                if ($like->getEntityId() == $customerId && $like->getCommentId() == $cmtId) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $postId
     *
     * @return array
     */
    public function getPostComments($postId)
    {
        $result = [];
        $comments = $this->cmtFactory->create()->getCollection()
            ->addFieldToFilter('main_table.post_id', $postId);
        foreach ($comments as $comment) {
            $result[] = $comment->getData();
        }

        return $result;
    }

    /**
     * @param $postId
     * @param $action
     *
     * @return int
     */
    public function getPostLike($postId, $action)
    {
        /** @var PostLike $postLike */
        $postLike = $this->postLikeFactory->create();

        return $postLike->getCollection()->addFieldToFilter('post_id', $postId)
            ->addFieldToFilter('action', $action)->count();
    }

    /**
     * @param $comment
     *
     * @return string
     */
    public function commentHtml($comment)
    {
        $html = '';
        foreach (explode("\n", trim($comment)) as $value) {
            $html .= '<p>' . $value . '</p>';
        }

        return $html;
    }

    /**
     * @param $comments
     * @param $cmtId
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCommentsTree($comments, $cmtId)
    {
        $this->commentTree .= '<ul class="default-cmt__content__cmt-content row">';
        foreach ($comments as $comment) {
            if ($comment['reply_id'] == $cmtId && $comment['status'] == 1) {
                $isReply = (bool)$comment['is_reply'];
                $replyId = $isReply ? $comment['reply_id'] : '';
                if ($comment['entity_id'] == 0) {
                    $userName = $comment['user_name'];
                } else {
                    $userCmt = $this->getUserComment($comment['entity_id']);
                    $userName = $userCmt->getFirstName() . ' '
                        . $userCmt->getLastName();
                }
                $countLikes = $this->getCommentLikes($comment['comment_id']);
                $isLiked = ($this->isLiked($comment['comment_id'])) ? "mpblog-liked" : "mpblog-like";
                $this->commentTree .= '<li id="cmt-id-' . $comment['comment_id']
                    . '" class="default-cmt__content__cmt-content__cmt-row cmt-row-'
                    . $comment['comment_id'] . ' cmt-row col-md-12'
                    . ($isReply ? ' reply-row' : '') . '" data-cmt-id="'
                    . $comment['comment_id'] . '" ' . ($replyId
                        ? 'data-reply-id="' . $replyId . '"' : '') . '>
                                <div class="cmt-row__cmt-username">
                                    <span class="cmt-row__cmt-username username username__'
                    . $comment['comment_id'] . '">'
                    . $userName . '</span>
                                </div>
                                <div class="cmt-row__cmt-content">
                                   ' . $this->commentHtml($comment['content']) . '
                                </div>
                                <div class="cmt-row__cmt-interactions interactions">
                                    <div class="interactions__btn-actions">
                                        <a class="interactions__btn-actions action btn-like '
                    . $isLiked . '" data-cmt-id="'
                    . $comment['comment_id'] . '" click="1">
                                        <i class="fa fa-thumbs-up" aria-hidden="true"></i>
                                        <span class="count-like__like-text">'
                    . $countLikes . '</span></a>
                                        <a class="interactions__btn-actions action btn-reply" data-cmt-id="'
                    . $comment['comment_id'] . '">' . __('Reply') . '</a>
                                    </div>
                                    <div class="interactions__cmt-createdat">
                                        <span>' . $this->getDateFormat($comment['created_at']) . '</span>
                                    </div>
                                </div>';
                if ($comment['has_reply']) {
                    $this->commentTree .= $this->getCommentsTree(
                        $comments,
                        $comment['comment_id']
                    );
                }
                $this->commentTree .= '</li>';
            }
        }
        $this->commentTree .= '</ul>';
    }

    /**
     * get tag list
     *
     * @param Post $post
     *
     * @return string
     */
    public function getTagList($post)
    {
        $tagCollection = $post->getSelectedTagsCollection();
        $result = '';
        if (!empty($tagCollection)) {
            $listTags = [];
            foreach ($tagCollection as $tag) {
                $listTags[] = '<a class="mp-info" href="' . $this->getTagUrl($tag) . '">' . $tag->getName() . '</a>';
            }
            $result = implode(', ', $listTags);
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->customerUrl->getLoginUrl();
    }

    /**
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->customerUrl->getRegisterUrl();
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            if ($catId = $this->getRequest()->getParam('cat')) {
                $category = $this->categoryFactory->create()
                    ->load($catId);
                if ($category->getId()) {
                    $breadcrumbs->addCrumb($category->getUrlKey(), [
                        'label' => $category->getName(),
                        'title' => $category->getName(),
                        'link' => $this->helperData->getBlogUrl($category, Data::TYPE_CATEGORY)
                    ]);
                }
            }

            $post = $this->getPost();
            $breadcrumbs->addCrumb($post->getUrlKey(), [
                'label' => $post->getName(),
                'title' => $post->getName()
            ]);
        }
    }

    /**
     * @param bool $meta
     *
     * @return array|string
     */
    public function getBlogTitle($meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);
        $post = $this->getBlogObject();
        if (!$post) {
            return $blogTitle;
        }

        if ($meta) {
            if ($post->getMetaTitle()) {
                $blogTitle[] = $post->getMetaTitle();
            } else {
                $blogTitle[] = ucfirst($post->getName());
            }

            return $blogTitle;
        }

        return ucfirst($post->getName());
    }

    /**
     * @param $priority
     * @param $message
     *
     * @return string
     */
    public function getMessagesHtml($priority, $message)
    {
        /** @var $messagesBlock Messages */
        $messagesBlock = $this->_layout->createBlock(Messages::class);
        $messagesBlock->{$priority}(__($message));

        return $messagesBlock->toHtml();
    }
}
