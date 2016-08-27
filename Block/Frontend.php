<?php
/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 * @category  Mageplaza
 * @package   Mageplaza_Blog
 * @copyright Copyright (c) 2016
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Block;

use Magento\Framework\View\Element\Template;

use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Helper\Data as HelperData;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class Frontend extends Template
{
    protected $helperData;
    protected $objectManager;
    protected $storeManager;
    protected $localeDate;

    public function __construct(
        Context $context,
        HelperData $helperData,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
    
        $this->helperData    = $helperData;
        $this->objectManager = $objectManager;
        $this->storeManager  = $storeManager;
        $this->localeDate    = $context->getLocaleDate();
        parent::__construct($context, $data);
    }

    public function getCurrentPost()
    {
        return $this->helperData->getPost($this->getRequest()->getParam('id'));
    }

    public function getUrlByPost($post)
    {
        return $this->helperData->getUrlByPost($post);
    }

    public function getImageUrl($image)
    {
        return $this->helperData->getImageUrl($image);
    }

    public function getCreatedAtStoreDate($createdAt)
    {
        return $this->_localeDate->scopeDate($this->storeManager->getStore(), $createdAt, true);
    }

    public function getPostCategoryHtml($post)
    {
        return $this->helperData->getPostCategoryHtml($post);
    }

    public function getBlogConfig($code)
    {
        return $this->helperData->getBlogConfig($code);
    }

    protected function _prepareLayout()
    {
        $actionName  = $this->getRequest()->getFullActionName();
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $breadcrumbsLabel = ucfirst($this->helperData->getBlogConfig('general/url_prefix'));
        if ($breadcrumbs) {
            if ($actionName == 'blog_post_index') {
                $breadcrumbs->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Go to Home Page'),
                        'link'  => $this->_storeManager->getStore()->getBaseUrl()
                    ]
                )->addCrumb(
                    $this->helperData->getBlogConfig('general/url_prefix'),
                    ['label' => $breadcrumbsLabel, 'title' => $this->helperData->getBlogConfig('general/url_prefix')]
                );
                $this->applySeoCode();
            } elseif ($actionName == 'blog_post_view') {
                $post     = $this->getCurrentPost();
                $category = $post->getSelectedCategoriesCollection()->addFieldToFilter('enabled', 1)->getFirstItem();
                $breadcrumbs->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Go to Home Page'),
                        'link'  => $this->_storeManager->getStore()->getBaseUrl()
                    ]
                );
                $breadcrumbs->addCrumb(
                    $this->helperData->getBlogConfig('general/url_prefix'),
                    ['label' => $breadcrumbsLabel,
                     'title' => $this->helperData->getBlogConfig('general/url_prefix'),
                     'link'  => $this->_storeManager->getStore()->getBaseUrl() . $this->helperData->getBlogConfig('general/url_prefix')]
                );
                if ($category->getId()) {
                    $breadcrumbs->addCrumb(
                        $category->getUrlKey(),
                        ['label' => ucfirst($category->getName()),
                         'title' => $category->getName(),
                         'link'  => $this->helperData->getCategoryUrl($category)]
                    );
                }
                $breadcrumbs->addCrumb(
                    $post->getUrlKey(),
                    ['label' => ucfirst($post->getName()),
                     'title' => $post->getName()]
                );
                $this->applySeoCode($post);
            } elseif ($actionName == 'blog_category_view') {
                $category = $this->helperData->getCategoryByParam('id', $this->getRequest()->getParam('id'));
                $breadcrumbs->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Go to Home Page'),
                        'link'  => $this->_storeManager->getStore()->getBaseUrl()
                    ]
                );
                $breadcrumbs->addCrumb(
                    $this->helperData->getBlogConfig('general/url_prefix'),
                    ['label' => $breadcrumbsLabel,
                     'title' => $this->helperData->getBlogConfig('general/url_prefix'),
                     'link'  => $this->_storeManager->getStore()->getBaseUrl() . $this->helperData->getBlogConfig('general/url_prefix')]
                )->addCrumb(
                    $category->getUrlKey(),
                    ['label' => ucfirst($category->getName()),
                     'title' => $category->getName(),
                    ]
                );
                $this->applySeoCode();
            } elseif ($actionName == 'blog_tag_view') {
                $tag = $this->helperData->getTagByParam('id', $this->getRequest()->getParam('id'));
                $breadcrumbs->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Go to Home Page'),
                        'link'  => $this->_storeManager->getStore()->getBaseUrl()
                    ]
                )->addCrumb(
                    $this->helperData->getBlogConfig('general/url_prefix'),
                    ['label' => $breadcrumbsLabel,
                     'title' => $this->helperData->getBlogConfig('general/url_prefix'),
                     'link'  => $this->_storeManager->getStore()->getBaseUrl() . $this->helperData->getBlogConfig('general/url_prefix')]
                )->addCrumb(
                    'Tag',
                    ['label' => 'Tag',
                     'title' => 'Tag']
                )->addCrumb(
                    'Tag' . $tag->getId(),
                    ['label' => ucfirst($tag->getName()),
                     'title' => $tag->getName()]
                );
                $this->applySeoCode();
            } elseif ($actionName == 'blog_topic_view') {
                $topic = $this->helperData->getTopicByParam('id', $this->getRequest()->getParam('id'));
                $breadcrumbs->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Go to Home Page'),
                        'link'  => $this->_storeManager->getStore()->getBaseUrl()
                    ]
                )->addCrumb(
                    $this->helperData->getBlogConfig('general/url_prefix'),
                    ['label' => $breadcrumbsLabel,
                     'title' => $this->helperData->getBlogConfig('general/url_prefix'),
                     'link'  => $this->_storeManager->getStore()->getBaseUrl() . $this->helperData->getBlogConfig('general/url_prefix')]
                )->addCrumb(
                    'Topic',
                    ['label' => 'Topic',
                     'title' => 'Topic']
                )->addCrumb(
                    'topic' . $topic->getId(),
                    ['label' => ucfirst($topic->getName()),
                     'title' => $topic->getName()]
                );
                $this->applySeoCode($topic);
            }
        }


        return parent::_prepareLayout();
    }

    public function applySeoCode($post = null)
    {
        if ($post) {
            $title = $post->getMetaTitle();
            if ($title) {
                $this->pageConfig->getTitle()->set($title);
            } else {
                $this->pageConfig->getTitle()->set($post->getName());
            }
            $description = $post->getMetaDescription();
            if ($description) {
                $this->pageConfig->setDescription($description);
            }
            $keywords = $post->getMetaKeywords();
            if ($keywords) {
                $this->pageConfig->setKeywords($keywords);
            }
            $robot      = $post->getMetaRobots();
            $robotModel = $this->objectManager->get('Mageplaza\Blog\Model\Post\Source\MetaRobots');
            $array      = $robotModel->getOptionArray();
            if ($keywords) {
                $this->pageConfig->setRobots($array[$robot]);
            }
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($post->getName());
            }
        } else {
            $title = $this->helperData->getBlogConfig('seo/meta_title');
            if ($title) {
                $this->pageConfig->getTitle()->set($title);
            }
            $description = $this->helperData->getBlogConfig('seo/meta_description');
            if ($description) {
                $this->pageConfig->setDescription($description);
            }
            $keywords = $this->helperData->getBlogConfig('seo/meta_keywords');
            if ($keywords) {
                $this->pageConfig->setKeywords($keywords);
            }
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($this->helperData->getBlogConfig('general/name'));
            }
        }
    }
}
