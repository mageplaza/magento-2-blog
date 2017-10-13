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

namespace Mageplaza\Blog\Block;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Data as HelperData;
use Mageplaza\Blog\Model\CommentFactory;
use Mageplaza\Blog\Model\Config\Source\DisplayType;
use Mageplaza\Blog\Model\LikeFactory;

/**
 * Class Frontend
 *
 * @package Mageplaza\Blog\Block
 */
class Frontend extends Template
{
    /**
     * @var FilterProvider
     */
    public $filterProvider;

    /**
     * @type \Mageplaza\Blog\Helper\Data
     */
    public $helperData;

    /**
     * @type \Magento\Store\Model\StoreManagerInterface
     */
    public $store;

    /**
     * @var \Mageplaza\Blog\Model\CommentFactory
     */
    public $cmtFactory;

    /**
     * @var \Mageplaza\Blog\Model\LikeFactory
     */
    public $likeFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * @var
     */
    public $commentTree;

    /**
     * Frontend constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Mageplaza\Blog\Model\CommentFactory $commentFactory
     * @param \Mageplaza\Blog\Model\LikeFactory $likeFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Mageplaza\Blog\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        FilterProvider $filterProvider,
        CommentFactory $commentFactory,
        LikeFactory $likeFactory,
        CustomerRepositoryInterface $customerRepository,
        HelperData $helperData,
        array $data = []
    )
    {
        $this->filterProvider     = $filterProvider;
        $this->cmtFactory         = $commentFactory;
        $this->likeFactory        = $likeFactory;
        $this->customerRepository = $customerRepository;
        $this->helperData         = $helperData;
        $this->store              = $context->getStoreManager();

        parent::__construct($context, $data);
    }

    /**
     * @return array|string
     */
    public function getPostCollection()
    {
        $collection = $this->getCollection();

        if ($collection->getSize()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'mpblog.post.pager');

            $blogLimit = (int)$this->getBlogConfig('general/pagination');
            $pager->setLimit($blogLimit ?: 10)
                ->setCollection($collection);

            $this->setChild('pager', $pager);
        }

        return $collection;
    }

    /**
     * Override this function to apply collection for each type
     *
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    protected function getCollection()
    {
        return $this->helperData->getPostCollection();
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return bool
     */
    public function isGridView()
    {
        return $this->helperData->getBlogConfig('general/display_style') == DisplayType::GRID;
    }

    /**
     * @param $image
     *
     * @return string
     */
    public function getImageUrl($image)
    {
        return $this->helperData->getImageHelper()->getMediaUrl($image);
    }

    /**
     * @return string
     */
    public function getRssUrl()
    {
        return $this->helperData->getBlogUrl('post/rss');
    }

    /**
     * @param $post
     *
     * @return null|string
     */
    public function getPostCategoryHtml($post)
    {
        return $this->helperData->getPostCategoryHtml($post);
    }

    /**
     * @param $code
     *
     * @return mixed
     */
    public function getBlogConfig($code)
    {
        return $this->helperData->getBlogConfig($code);
    }

    /**
     * @param $code
     * @param null $storeId
     * @return mixed
     */
    public function getSeoConfig($code, $storeId = null)
    {
        return $this->helperData->getSeoConfig($code, $storeId);
    }

    /**
     * @return array
     */
    protected function getBreadcrumbsData()
    {
        $label = $this->helperData->getBlogName();

        $data = [
            'label' => $label,
            'title' => $label,
            'link'  => $this->helperData->getBlogUrl()
        ];

        if ($this->getRequest()->getFullActionName() == 'mpblog_post_index') {
            unset($data['link']);
        }

        return $data;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link'  => $this->_storeManager->getStore()->getBaseUrl()
            ])
                ->addCrumb($this->helperData->getRoute(), $this->getBreadcrumbsData());

            $this->applySeoCode();
        }
        $actionName       = $this->getRequest()->getFullActionName();
        $breadcrumbs      = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbsLabel = ucfirst($this->helperData->getBlogName());
        $breadcrumbsLink  = $this->helperData->getRoute();
        if ($breadcrumbs) {
            if ($actionName == 'mpblog_category_view') {
                $category = $this->helperData->getCategoryByParam(
                    'id',
                    $this->getRequest()->getParam('id')
                );
                $breadcrumbs->addCrumb(
                    'home',
                    ['label' => __('Home'), 'title' => __('Go to Home Page'),
                     'link'  => $this->_storeManager->getStore()->getBaseUrl()]
                );
                $breadcrumbs->addCrumb(
                    $this->helperData->getBlogConfig('general/url_prefix'),
                    ['label'   => $breadcrumbsLabel,
                     'title'   => $this->helperData->getBlogConfig(
                         'general/url_prefix'
                     ), 'link' => $this->_storeManager->getStore()->getBaseUrl()
                        . $breadcrumbsLink]
                )->addCrumb(
                    $category->getUrlKey(),
                    ['label' => ucfirst($category->getName()),
                     'title' => $category->getName(),]
                );
                $this->applySeoCode($category);
            } elseif ($actionName == 'mpblog_tag_view') {
                $tag = $this->helperData->getTagByParam(
                    'id',
                    $this->getRequest()->getParam('id')
                );
                $breadcrumbs->addCrumb(
                    'home',
                    ['label' => __('Home'), 'title' => __('Go to Home Page'),
                     'link'  => $this->_storeManager->getStore()->getBaseUrl()]
                )->addCrumb(
                    $this->helperData->getBlogConfig('general/url_prefix'),
                    ['label'   => $breadcrumbsLabel,
                     'title'   => $this->helperData->getBlogConfig(
                         'general/url_prefix'
                     ), 'link' => $this->_storeManager->getStore()->getBaseUrl()
                        . $breadcrumbsLink]
                )->addCrumb(
                    'Tag' . $tag->getId(),
                    ['label' => ucfirst($tag->getName()),
                     'title' => $tag->getName()]
                );
                $this->applySeoCode($tag);
            } elseif ($actionName == 'mpblog_topic_view') {
                $topic = $this->helperData->getTopicByParam(
                    'id',
                    $this->getRequest()->getParam('id')
                );
                $breadcrumbs->addCrumb(
                    'home',
                    ['label' => __('Home'), 'title' => __('Go to Home Page'),
                     'link'  => $this->_storeManager->getStore()->getBaseUrl()]
                )->addCrumb(
                    $this->helperData->getBlogConfig('general/url_prefix'),
                    ['label'   => $breadcrumbsLabel,
                     'title'   => $this->helperData->getBlogConfig(
                         'general/url_prefix'
                     ), 'link' => $this->_storeManager->getStore()->getBaseUrl()
                        . $breadcrumbsLink]
                )->addCrumb(
                    'topic' . $topic->getId(),
                    ['label' => ucfirst($topic->getName()),
                     'title' => $topic->getName()]
                );
                $this->applySeoCode($topic);
            } elseif ($actionName == 'mpblog_author_view') {
                $author = $this->helperData->getAuthorByParam(
                    'id',
                    $this->getRequest()->getParam('id')
                );
                $breadcrumbs->addCrumb(
                    'home',
                    ['label' => __('Home'), 'title' => __('Go to Home Page'),
                     'link'  => $this->_storeManager->getStore()->getBaseUrl()]
                )->addCrumb(
                    $this->helperData->getBlogConfig('general/url_prefix'),
                    ['label'   => $breadcrumbsLabel,
                     'title'   => $this->helperData->getBlogConfig(
                         'general/url_prefix'
                     ), 'link' => $this->_storeManager->getStore()->getBaseUrl()
                        . $breadcrumbsLink]
                )->addCrumb(
                    'author' . $author->getId(),
                    ['label' => __('Author'), 'title' => __('Author')]
                );
                $pageMainTitle = $this->getLayout()->getBlock(
                    'page.main.title'
                );
                $pageMainTitle->setPageTitle('About Author');
                $this->pageConfig->getTitle()->set($author->getName());
            } elseif ($actionName == 'mpblog_sitemap_index') {
                $breadcrumbs->addCrumb(
                    'home',
                    ['label' => __('Home'), 'title' => __('Go to Home Page'),
                     'link'  => $this->_storeManager->getStore()->getBaseUrl()]
                )->addCrumb(
                    $this->helperData->getBlogConfig('general/url_prefix'),
                    ['label'   => $breadcrumbsLabel,
                     'title'   => $this->helperData->getBlogConfig(
                         'general/url_prefix'
                     ), 'link' => $this->_storeManager->getStore()->getBaseUrl()
                        . $breadcrumbsLink]
                )->addCrumb(
                    'SiteMap',
                    ['label' => __('Sitemap'), 'title' => __('Sitemap')]
                );
                $pageMainTitle = $this->getLayout()->getBlock(
                    'page.main.title'
                );
                $pageMainTitle->setPageTitle('Site Map');
                $this->pageConfig->getTitle()->set('Site Map');
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * @return $this
     */
    public function applySeoCode()
    {
        $title = $this->getSeoConfig('meta_title') ?: $this->helperData->getBlogConfig('general/name');
        $this->pageConfig->getTitle()->set($title ?: __('Blog'));

        $description = $this->getSeoConfig('meta_description');
        $this->pageConfig->setDescription($description);

        $keywords = $this->getSeoConfig('meta_keywords');
        $this->pageConfig->setKeywords($keywords);

        $pageTitle     = $this->helperData->getBlogConfig('general/name');
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($pageTitle);
        }

        return $this;
    }

    /**
     * get sidebar config
     *
     * @param $code
     * @param $storeId
     *
     * @return mixed
     */
    public function getSidebarConfig($code, $storeId = null)
    {
        return $this->helperData->getSidebarConfig($code, $storeId);
    }

    /**
     * @param $post
     * @return \Magento\Framework\Phrase|string
     */
    public function getPostInfo($post)
    {
        $html = __('Posted on %1', $this->getDateFormat($post->getPublishDate()));

        if ($categoryPost = $this->getPostCategoryHtml($post)) {
            $html .= __('| Posted in %1', $categoryPost);
        }

        $author = $this->helperData->getAuthorByPost($post);
        if ($author && $author->getName() && $this->helperData->showAuthorInfo()) {
            $aTag = '<a class="mp-info" href="' . $author->getUrl() . '">' . $this->escapeHtml($author->getName()) . '</a>';
            $html .= __('| By: %1', $aTag);
        }

        return $html;
    }

    /**
     * check customer is logged in or not
     */
    public function isLoggedIn()
    {
        return $this->helperData->isLoggedIn();
    }

    /**
     * get login url
     */
    public function getLoginUrl()
    {
        return $this->helperData->getLoginUrl();
    }

    /**
     * @param $postId
     * @return array
     */
    public function getPostComments($postId)
    {
        $result   = [];
        $comments = $this->cmtFactory->create()->getCollection()
            ->addFieldToFilter('post_id', $postId);
        foreach ($comments as $comment) {
            array_push($result, $comment->getData());
        }

        return $result;
    }

    /**
     * get comments tree
     *
     * @param $comments
     * @param $cmtId
     */
    public function getCommentsTree($comments, $cmtId)
    {
        $this->commentTree .= '<ul class="default-cmt__content__cmt-content row">';
        foreach ($comments as $comment) {
            if ($comment['reply_id'] == $cmtId) {
                $isReply           = (bool)$comment['is_reply'];
                $replyId           = $isReply ? $comment['reply_id'] : '';
                $userCmt           = $this->getUserComment($comment['entity_id']);
                $userName          = $userCmt->getFirstName() . ' '
                    . $userCmt->getLastName();
                $countLikes        = $this->getCommentLikes($comment['comment_id']);
                $this->commentTree .= '<li class="default-cmt__content__cmt-content__cmt-row cmt-row col-xs-12'
                    . ($isReply ? ' reply-row' : '') . '" data-cmt-id="'
                    . $comment['comment_id'] . '" ' . ($replyId
                        ? 'data-reply-id="' . $replyId . '"' : '') . '>
							<div class="cmt-row__cmt-username">
								<span class="cmt-row__cmt-username username">'
                    . $userName . '</span>
							</div>
							<div class="cmt-row__cmt-content">
								<p>' . $comment['content'] . '</p>
							</div>
							<div class="cmt-row__cmt-interactions interactions">
								<div class="interactions__btn-actions">
									<a class="interactions__btn-actions action btn-like" data-cmt-id="'
                    . $comment['comment_id'] . '">' . __('Like') . '</a>
									<a class="interactions__btn-actions action btn-reply" data-cmt-id="'
                    . $comment['comment_id'] . '">' . __('Reply') . '</a>
									<a class="interactions__btn-actions count-like">
										<i class="fa fa-thumbs-up" aria-hidden="true"></i>
										<span class="count-like__like-text">'
                    . $countLikes . '</span>
									</a>
								</div>
								<div class="interactions__cmt-createdat">
									<span>' . $comment['created_at'] . '</span>
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
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getUserComment($userId)
    {
        $user = $this->customerRepository->getById($userId);

        return $user;
    }

    /**
     * @param $cmtId
     * @return int|string
     */
    public function getCommentLikes($cmtId)
    {
        $likes = $this->likeFactory->create()->getCollection()
            ->addFieldToFilter('comment_id', $cmtId)->getSize();
        if ($likes) {
            return $likes;
        }

        return '';
    }

    /**
     * get default image url
     */
    public function getDefaultImageUrl()
    {
        return $this->getViewFileUrl('Mageplaza_Blog::media/images/mageplaza-logo-default.png');
    }

    /**
     * @param $date
     * @param bool $monthly
     * @return false|string
     */
    public function getDateFormat($date, $monthly = false)
    {
        return $this->helperData->getDateFormat($date, $monthly);
    }

    /**
     * @return mixed
     */
    public function getMonthParam()
    {
        return $this->getRequest()->getParam('month');
    }

    /**
     * Resize Image Function
     * @param $image
     * @param null $width
     * @param null $height
     * @return string
     */
    public function resizeImage($image, $width = null, $height = null)
    {
        if (is_null($width)) {
            $width  = $this->helperData->getBlogConfig('general/resize_image/resize_width');
            $height = $this->helperData->getBlogConfig('general/resize_image/resize_height');
        }

        return $this->helperData->getImageHelper()->resizeImage($image, $width, $height);
    }
}
