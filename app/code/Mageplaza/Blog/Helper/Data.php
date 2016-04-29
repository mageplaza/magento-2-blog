<?php

namespace Mageplaza\Blog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\Blog\Model\PostFactory;

class Data extends AbstractHelper
{
	const XML_PATH_BLOG = 'blog/';
	protected $storeManager;
	protected $objectManager;
	protected $postfactory;

	public function __construct(
		Context $context,
		ObjectManagerInterface $objectManager,
		StoreManagerInterface $storeManager,
		PostFactory $postFactory
	)
	{
		$this->objectManager = $objectManager;
		$this->storeManager  = $storeManager;
		$this->postfactory   = $postFactory;
		parent::__construct($context);
	}

	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field,
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

	public function getBlogConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_BLOG . $code, $storeId);
	}

	public function getPostList()
	{
		$posts = $this->postfactory->create();
		$list  = $posts->getCollection()
			->addFieldToFilter('enabled', 1);

		return $list;
	}

	public function getUrlByPost($post)
	{
		if ($post->getUrlKey()) {
			$url_prefix = $this->getBlogConfig('general/url_prefix');
			$url_suffix = $this->getBlogConfig('general/url_suffix');

			$urlKey = '';
			if ($url_prefix) {
				$urlKey .= $url_prefix . '/';
			}
			$urlKey .= $post->getUrlKey();
			if ($url_suffix) {
				$urlKey .= $url_suffix;
			}
		}

		return $this->_getUrl($urlKey);
	}

	public function getPostByUrl($url)
	{
		return true;
	}

	public function getPostsByTag($tag)
	{
		$posts      = $this->postfactory->create();
		$collection = $posts->getCollection();

		return $collection;
	}

	public function getPostsByCategory($category)
	{
		$collection = true;

		return $collection;
	}

	public function getImageUrl($image)
	{
		return $this->getBaseMediaUrl() . '/' . $image;
	}

	public function getBaseMediaUrl()
	{
		return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}

}