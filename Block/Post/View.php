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

namespace Mageplaza\Blog\Block\Post;

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Data as HelperData;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\CommentFactory;
use Mageplaza\Blog\Model\LikeFactory;
use Mageplaza\Blog\Model\Post;
use Mageplaza\Blog\Model\PostFactory;

/**
 * Class View
 * @package Mageplaza\Blog\Block\Post
 * @method Post getPost()
 * @method void setPost($post)
 */
class View extends Frontend
{
    /**
     * config logo blog path
     */
    const LOGO = 'mageplaza/blog/logo/';

    /**
     * @var \Mageplaza\Blog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Mageplaza\Blog\Model\PostFactory
     */
    protected $postFactory;

    /**
     * View constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Mageplaza\Blog\Model\CommentFactory $commentFactory
     * @param \Mageplaza\Blog\Model\LikeFactory $likeFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Mageplaza\Blog\Helper\Data $helperData
     * @param \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
     * @param \Mageplaza\Blog\Model\PostFactory $postFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        FilterProvider $filterProvider,
        CommentFactory $commentFactory,
        LikeFactory $likeFactory,
        CustomerRepositoryInterface $customerRepository,
        HelperData $helperData,
        CategoryFactory $categoryFactory,
        PostFactory $postFactory,
        array $data = []
    )
    {
        $this->categoryFactory = $categoryFactory;
        $this->postFactory     = $postFactory;

        parent::__construct($context, $filterProvider, $commentFactory, $likeFactory, $customerRepository, $helperData, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        parent::_construct();

        $post = $this->postFactory->create();
        if ($id = $this->getRequest()->getParam('id')) {
            $post->load($id);
        }
        $this->setPost($post);
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
     * @return string
     */
    public function getTopicUrl($topic)
    {
        return $this->helperData->getBlogUrl($topic, Data::TYPE_TOPIC);
    }

    /**
     * @param $tag
     * @return string
     */
    public function getTagUrl($tag)
    {
        return $this->helperData->getBlogUrl($tag, Data::TYPE_TAG);
    }

    /**
     * @param $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        return $this->helperData->getBlogUrl($category, Data::TYPE_CATEGORY);
    }

    /**
     * @param $code
     * @return mixed
     */
    public function helperComment($code)
    {
        return $this->helperData->getBlogConfig('comment/' . $code);
    }

    /**
     * get tag list
     * @param $post
     * @return string
     */
    public function getTagList($post)
    {
        $tagCollection = $post->getSelectedTagsCollection();
        $result        = '';
        if (!empty($tagCollection)) :
            $listTags = [];
            foreach ($tagCollection as $tag) {
                $listTags[] = '<a class="mp-info" href="' . $this->getTagUrl($tag) . '">' . $tag->getName() . '</a>';
            }
            $result = implode(', ', $listTags);
        endif;

        return $result;
    }

    /**
     * @param $content
     * @return string
     */
    public function getPageFilter($content)
    {
        return $this->filterProvider->getPageFilter()->filter($content);
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
                            'link'  => $this->helperData->getBlogUrl($category, Data::TYPE_CATEGORY)
                        ]
                    );
                }
            }

            $post = $this->getPost();
            $breadcrumbs->addCrumb($post->getUrlKey(), [
                    'label' => $post->getName(),
                    'title' => $post->getName()
                ]
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function applySeoCode()
    {
        $post = $this->getPost();

        $title = $post->getMetaTitle() ?: $post->getName();
        $this->pageConfig->getTitle()->set($title ?: __('Blog'));

        $this->pageConfig->setDescription($post->getMetaDescription());
        $this->pageConfig->setKeywords($post->getMetaKeywords());
        $this->pageConfig->setRobots($post->getMetaRobots());

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($post->getName());
        }

        return $this;
    }
}
