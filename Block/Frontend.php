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
namespace Mageplaza\Blog\Block;

use Magento\Framework\View\Element\Template;

use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Helper\Data as HelperData;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template\Context as TemplateContext;
use Magento\Cms\Model\Template\FilterProvider;

/**
 * Class Frontend
 * @package Mageplaza\Blog\Block
 */
class Frontend extends Template
{
	/**
	 * @type \Mageplaza\Blog\Helper\Data
	 */
	public $helperData;

	/**
	 * @type \Magento\Store\Model\StoreManagerInterface
	 */
	public $store;

	/**
	 * @type \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	public $dateTime;

	/**
	 * @type \Mageplaza\Blog\Model\Post\Source\MetaRobots
	 */
	public $mpRobots;
	public $filterProvider;
	/**
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
	 * @param \Mageplaza\Blog\Model\Post\Source\MetaRobots $metaRobots
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Mageplaza\Blog\Helper\Data $helperData
	 * @param \Magento\Framework\View\Element\Template\Context $templateContext
	 * @param array $data
	 */
	public function __construct(
		\Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Mageplaza\Blog\Model\Post\Source\MetaRobots $metaRobots,
		Context $context,
		HelperData $helperData,
		TemplateContext $templateContext,
		FilterProvider $filterProvider,
		array $data = []
	)
	{
		$this->dateTime   = $dateTime;
		$this->mpRobots   = $metaRobots;
		$this->helperData = $helperData;
		$this->store      = $templateContext->getStoreManager();
		$this->filterProvider = $filterProvider;
		parent::__construct($context, $data);
	}

	/**
	 * @return mixed
	 */
	public function getCurrentPost()
	{
		return $this->helperData->getPost($this->getRequest()->getParam('id'));
	}

	/**
	 * @param $post
	 * @return string
	 */
	public function getUrlByPost($post)
	{
		return $this->helperData->getUrlByPost($post);
	}

	/**
	 * @param $image
	 * @return string
	 */
	public function getImageUrl($image)
	{
		return $this->helperData->getImageUrl($image);
	}

	/**
	 * @param $createdAt
	 * @return \DateTime
	 */
	public function getCreatedAtStoreDate($createdAt)
	{
		return $this->_localeDate->scopeDate($this->_storeManager->getStore(), $createdAt, true);
	}

	/**
	 * @param $post
	 * @return null|string
	 */
	public function getPostCategoryHtml($post)
	{
		return $this->helperData->getPostCategoryHtml($post);
	}

	/**
	 * @param $code
	 * @return mixed
	 */
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
		$storeId     = $this->store->getStore()->getId();
		$postStoreId = $post->getStoreIds() != null ? explode(',', $post->getStoreIds()) : '-1';
		if (is_array($postStoreId) && (in_array($storeId, $postStoreId) || in_array('0', $postStoreId))) {
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

	/**
	 * @return $this
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	protected function _prepareLayout()
	{
		$actionName       = $this->getRequest()->getFullActionName();
		$breadcrumbs      = $this->getLayout()->getBlock('breadcrumbs');
		$breadcrumbsLabel = ucfirst($this->helperData->getBlogConfig('general/name')
			?: \Mageplaza\Blog\Helper\Data::DEFAULT_URL_PREFIX);
		if ($breadcrumbs) {
			if ($actionName == 'mpblog_post_index') {
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
			} elseif ($actionName == 'mpblog_post_view') {
				$post = $this->getCurrentPost();
				if ($this->filterPost($post)) {
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
						['label' => __('Post'),
						 'title' => __('Post')]
					);
					$this->applySeoCode($post);
				}
			} elseif ($actionName == 'mpblog_category_view') {
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
			} elseif ($actionName == 'mpblog_tag_view') {
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
					'Tag' . $tag->getId(),
					['label' => ucfirst($tag->getName()),
					 'title' => $tag->getName()]
				);
				$this->applySeoCode($tag);
			} elseif ($actionName == 'mpblog_topic_view') {
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
					'topic' . $topic->getId(),
					['label' => ucfirst($topic->getName()),
					 'title' => $topic->getName()]
				);
				$this->applySeoCode($topic);
			}
		}


		return parent::_prepareLayout();
	}

	/**
	 * @return string
	 */
	public function getPagerHtml()
	{
		return $this->getChildHtml('pager');
	}

	/**
	 * @param null $post
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function applySeoCode($post = null)
	{
		if ($post) {
			$title = $post->getMetaTitle();
			$this->setPageData($title, 1, $post->getName());

			$description = $post->getMetaDescription();
			$this->setPageData($description, 2);

			$keywords = $post->getMetaKeywords();
			$this->setPageData($keywords, 3);

			$robot = $post->getMetaRobots();
			$array = $this->mpRobots->getOptionArray();
			if ($keywords) {
				$this->setPageData($array[$robot], 4);
			}
			$pageMainTitle = $this->getLayout()->getBlock('page.main.title');
			if ($pageMainTitle) {
				$pageMainTitle->setPageTitle($post->getName());
			}
		} else {
			$title = $this->helperData->getBlogConfig('seo/meta_title')
				?: $this->helperData->getBlogConfig('general/name');
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
	 * @param $data
	 * @param $type
	 * @param null $name
	 * @return string|void
	 */
	public function setPageData($data, $type, $name = null)
	{
		if ($data) {
			return $this->setDataFromType($data, $type);
		}

		return $this->setDataFromType($name, $type);
	}

	/**
	 * @param $data
	 * @param $type
	 * @return $this|string|void
	 */
	public function setDataFromType($data, $type)
	{
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
	 * @param null $type
	 * @param null $id
	 * @return array|string
	 */
	public function getBlogPagination($type = null, $id = null)
	{
		$page     = $this->getRequest()->getParam('p');
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
			$limit     = (int)$this->getBlogConfig('general/pagination') ?: 1;
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
	 * @param null $page
	 * @param $numOfPage
	 * @param $limit
	 * @param array $array
	 * @return array
	 */
	public function getPostPerPage($page = null, $numOfPage, $limit, $array = array())
	{
		$results    = array();
		$firstIndex = 0;
		$lastIndex  = $limit - 1;
		if ($page) {
			if ($page > $numOfPage || $page < 1) {
				$page = 1;
			}

			$firstIndex = $limit * $page - $limit;
			$lastIndex  = $firstIndex + $limit - 1;
			if (!isset($array[$lastIndex])) {
				for ($i = $lastIndex; $i >= $firstIndex; $i--) {
					if (isset($array[$i])) {
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

	/**
	 * get sidebar config
	 * @param $code
	 * @param $storeId
	 * @return mixed
	 */
	public function getSidebarConfig($code, $storeId = null)
	{
		return $this->helperData->getSidebarConfig($code, $storeId);
	}

	/**
	 * get html sitemap url
	 */
	public function getHtmlSiteMapUrl()
	{
		$moduleRoute = $this->helperData->getBlogConfig('general/url_prefix');
		if ($moduleRoute) {
			return $this->getBaseUrl() . $moduleRoute .'/sitemap/';
		}

		return $this->getBaseUrl() .'/mpblog/sitemap/';
	}
	public function getAuthorByPost($authorId)
	{
		return $this->helperData->getAuthorByPost($authorId);
	}
}
