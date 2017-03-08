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
use Magento\Framework\View\Element\Template\Context as TemplateContext;

class Frontend extends Template
{
	public $helperData;
	public $store;
	public $dateTime;
	public $mpRobots;

    public function __construct(
		\Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Mageplaza\Blog\Model\Post\Source\MetaRobots $metaRobots,
        Context $context,
        HelperData $helperData,
        TemplateContext $templateContext,
        array $data = []
    ) {
    	$this->dateTime = $dateTime;
    	$this->mpRobots = $metaRobots;
        $this->helperData    = $helperData;
        $this->store = $templateContext->getStoreManager();
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
        return $this->_localeDate->scopeDate($this->_storeManager->getStore(), $createdAt, true);
    }

    public function getPostCategoryHtml($post)
    {
        return $this->helperData->getPostCategoryHtml($post);
    }

    public function getBlogConfig($code)
    {
        return $this->helperData->getBlogConfig($code);
    }

    /**
     * filter post by store
     * return true/false
     */
    public function filterPost($post)
    {
        $storeId = $this->store->getStore()->getId();
        $postStoreId = $post->getStoreIds() ? explode(',', $post->getStoreIds()) : '-1';
        if (in_array($storeId, $postStoreId)) {
            return true;
        }
        return false;
    }

    /**
     * format post created_at
     */
    public function formatCreatedAt($createdAt)
    {
        $dateFormat = date('Y-m-d', $this->dateTime->timestamp($createdAt));
        return $dateFormat;
    }

    protected function _prepareLayout()
    {
        $actionName       = $this->getRequest()->getFullActionName();
        $breadcrumbs      = $this->getLayout()->getBlock('breadcrumbs');
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
                     'link'  => $this->_storeManager->getStore()->getBaseUrl()
						 . $this->helperData->getBlogConfig('general/url_prefix')]
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
                     'link'  => $this->_storeManager->getStore()->getBaseUrl()
						 . $this->helperData->getBlogConfig('general/url_prefix')]
                )->addCrumb(
                    $category->getUrlKey(),
                    ['label' => ucfirst($category->getName()),
                     'title' => $category->getName(),
                    ]
                );
                $this->applySeoCode($category);
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
                     'link'  => $this->_storeManager->getStore()->getBaseUrl()
						 . $this->helperData->getBlogConfig('general/url_prefix')]
                )->addCrumb(
                    'Tag',
                    ['label' => 'Tag',
                     'title' => 'Tag']
                )->addCrumb(
                    'Tag' . $tag->getId(),
                    ['label' => ucfirst($tag->getName()),
                     'title' => $tag->getName()]
                );
                $this->applySeoCode($tag);
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
                     'link'  => $this->_storeManager->getStore()->getBaseUrl()
						 . $this->helperData->getBlogConfig('general/url_prefix')]
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

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function applySeoCode($post = null)
    {
        if ($post) {
            $title = $post->getMetaTitle();
			$this->setPageData($title, 1, $post->getName());

            $description = $post->getMetaDescription();
            $this->setPageData($description, 2);

            $keywords = $post->getMetaKeywords();
            $this->setPageData($keywords, 3);

            $robot      = $post->getMetaRobots();
            $array      = $this->mpRobots->getOptionArray();
            if ($keywords) {
            	$this->setPageData($array[$robot], 4);
            }
            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($post->getName());
            }
        } else {
            $title = $this->helperData->getBlogConfig('general/name');
            $this->setPageData($title, 1, __('Blog'));

            $description = $this->helperData->getBlogConfig('seo/meta_description');
            $this->setPageData($description, 2);

            $keywords = $this->helperData->getBlogConfig('seo/meta_keywords');
            $this->setPageData($keywords, 3);

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($this->helperData->getBlogConfig('general/name'));
            }
        }
    }

    /**
	 * return set page data
	 */
    public function setPageData($data, $type, $name = null){
    	if ($data) {
			return $this->setDataFromType($data, $type);
		}

		return $this->setDataFromType($name, $type);
	}

	/**
	 * set page data based on type
	 */
	public function setDataFromType($data, $type){
		switch ($type) {
			case 1:
				return $this->pageConfig->getTitle()->set($data);
				break;
			case 2:
				return $this->pageConfig->setDescription($data);
				break;
			case 3:
				return $this->pageConfig->setKeywords($data);
				break;
			case 4:
				return $this->pageConfig->setRobots($data);
				break;
		}

		return '';
	}

	/**
	 * @return array|string
	 */
	public function getBlogPagination($type = null, $id = null)
	{
		$page = $this->getRequest()->getParam('p');
		$postList = '';
		if ($type == null) {
			$postList = $this->helperData->getPostList();
		} elseif ($type == 'category') {
			$postList = $this->helperData->getPostList('category', $id);
		} elseif ($type == 'tag') {
			$postList = $this->helperData->getPostList('tag', $id);
		} elseif ($type == 'topic') {
			$postList = $this->helperData->getPostList('topic', $id);
		}

		if ($postList != '' && is_array($postList)) {
			$limit = (int) $this->getBlogConfig('general/pagination');
			$numOfPost = count($postList);
			$numOfPage = 1;
			$countPost = count($postList);
			if ($countPost > $limit) {
				$numOfPage = ($numOfPost % $limit != 0) ? ($numOfPost / $limit) + 1 : ($numOfPost / $limit);

				return $this->getPostPerPage($page, $numOfPage, $limit, $postList);
			}

			array_unshift($postList, $numOfPage);
			return $postList;
		}

		return '';
	}

	/**
	 * get posts per page
	 */
	public function getPostPerPage($page = null, $numOfPage, $limit, $array = array()){
		$results = array();
		$firstIndex = 0;
		$lastIndex = $limit - 1;
		if ($page) {
			if($page > $numOfPage || $page < 1){
				$page = 1;
			}

			$firstIndex = $limit * $page - $limit;
			$lastIndex = $firstIndex + $limit - 1;
			if (!isset($array[$lastIndex])) {
				for ($i = $lastIndex; $i >= $firstIndex; $i--) {
					if(isset($array[$i])){
						$lastIndex = $i;
						break;
					}
				}
			}
		}

		for ($i = $firstIndex; $i <= $lastIndex; $i++) {
			array_push($results, $array[$i]);
		}

		array_unshift($results, $numOfPage);
		return $results;
	}
}
