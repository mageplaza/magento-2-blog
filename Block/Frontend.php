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

        if ($collection && $collection->getSize()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'mpblog.post.pager');

            $blogLimit = (int)$this->helperData->getBlogConfig('general/pagination');
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
     * @param $content
     * @return string
     */
    public function getPageFilter($content)
    {
        return $this->filterProvider->getPageFilter()->filter($content);
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
     * @inheritdoc
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
        }

        $this->applySeoCode();

        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    protected function getBreadcrumbsData()
    {
        $label = $this->helperData->getBlogName();

        $data = [
            'label' => $label,
            'title' => $label
        ];

        if ($this->getRequest()->getFullActionName() != 'mpblog_post_index') {
            $data['link'] = $this->helperData->getBlogUrl();
        }

        return $data;
    }

    /**
     * @return $this
     */
    public function applySeoCode()
    {
        $this->pageConfig->getTitle()->set(join($this->getTitleSeparator(), array_reverse($this->getBlogTitle(true))));

        $object = $this->getBlogObject();

        $description = $object ? $object->getMetaDescription() : $this->helperData->getSeoConfig('meta_description');
        $this->pageConfig->setDescription($description);

        $keywords = $object ? $object->getMetaKeywords() : $this->helperData->getSeoConfig('meta_keywords');
        $this->pageConfig->setKeywords($keywords);

        $robots = $object ? $object->getMetaRobots() : $this->helperData->getSeoConfig('meta_robots');
        $this->pageConfig->setRobots($robots);

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->getBlogTitle());
        }

        return $this;
    }

    /**
     * Retrieve HTML title value separator (with space)
     *
     * @return string
     */
    public function getTitleSeparator()
    {
        $separator = (string)$this->helperData->getConfigValue('catalog/seo/title_separator');

        return ' ' . $separator . ' ';
    }

    /**
     * @param bool $meta
     * @return array
     */
    public function getBlogTitle($meta = false)
    {
        $pageTitle = $this->helperData->getBlogConfig('general/name');
        if ($meta) {
            $title = $this->helperData->getSeoConfig('meta_title') ?: $pageTitle;

            return [$title];
        }

        return $pageTitle;
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
     * get list category html of post
     * @param $post
     * @return null|string
     */
    public function getPostCategoryHtml($post)
    {
        if (!$post->getCategoryIds()) {
            return null;
        }

        $categories   = $this->helperData->getCategoryCollection($post->getCategoryIds());
        $categoryHtml = [];
        foreach ($categories as $_cat) {
            $categoryHtml[] = '<a class="mp-info" href="' . $this->helperData->getBlogUrl($_cat, Data::TYPE_CATEGORY) . '">' . $_cat->getName() . '</a>';
        }
        $result = implode(', ', $categoryHtml);

        return $result;
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
     * Resize Image Function
     * @param $image
     * @param null $width
     * @param null $height
     * @return string
     */
    public function resizeImage($image, $width = null, $height = null)
    {
        if (!$image) {
            return $this->getDefaultImageUrl();
        }

        if (is_null($width)) {
            $width  = $this->helperData->getBlogConfig('general/resize_image/resize_width');
            $height = $this->helperData->getBlogConfig('general/resize_image/resize_height');
        }

        return $this->helperData->getImageHelper()->resizeImage($image, $width, $height);
    }

    /**
     * get default image url
     */
    public function getDefaultImageUrl()
    {
        return $this->getViewFileUrl('Mageplaza_Blog::media/images/mageplaza-logo-default.png');
    }

    /**
     * @return string
     */
    public function getDefaultAuthorImage()
    {
        return $this->getViewFileUrl('Mageplaza_Blog::media/images/no-artist-image.jpg');
    }
}
