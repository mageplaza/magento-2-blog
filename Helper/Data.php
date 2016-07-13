<?php

namespace Mageplaza\Blog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\TagFactory;
use Mageplaza\Blog\Model\TopicFactory;

class Data extends AbstractHelper
{
	const XML_PATH_BLOG = 'blog/';
	protected $storeManager;
	protected $objectManager;
	protected $postfactory;
	protected $categoryfactory;
	protected $tagfactory;
	protected $topicfactory;

	/**
	 * Data constructor.
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\ObjectManagerInterface $objectManager
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Mageplaza\Blog\Model\PostFactory $postFactory
	 * @param \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
	 * @param \Mageplaza\Blog\Model\TagFactory $tagFactory
	 * @param \Mageplaza\Blog\Model\TopicFactory $topicFactory
	 */
	public function __construct(
		Context $context,
		ObjectManagerInterface $objectManager,
		StoreManagerInterface $storeManager,
		PostFactory $postFactory,
		CategoryFactory $categoryFactory,
		TagFactory $tagFactory,
		TopicFactory $topicFactory
	)
	{
		$this->objectManager   = $objectManager;
		$this->storeManager    = $storeManager;
		$this->postfactory     = $postFactory;
		$this->categoryfactory = $categoryFactory;
		$this->tagfactory      = $tagFactory;
		$this->topicfactory    = $topicFactory;
		parent::__construct($context);
	}

	/**
	 * @param $field
	 * @param null $storeId
	 * @return mixed
	 */
	public function getConfigValue($field, $storeId = null)
	{
		return $this->scopeConfig->getValue(
			$field,
			ScopeInterface::SCOPE_STORE,
			$storeId
		);
	}

	/**
	 * @param $code
	 * @param null $storeId
	 * @return mixed
	 */
	public function getBlogConfig($code, $storeId = null)
	{
		return $this->getConfigValue(self::XML_PATH_BLOG . $code, $storeId);
	}

	/**
	 * @param null $type
	 * @param null $id
	 * @return $this|\Mageplaza\Blog\Model\Post
	 */
	public function getPostList($type = null, $id = null)
	{
		$list          = '';
		$posts         = $this->postfactory->create();
		$categoryModel = $this->categoryfactory->create();
		$tagModel      = $this->tagfactory->create();
		$topicModel    = $this->topicfactory->create();
		if ($type == null) {
			$list = $posts->getCollection();
		} elseif ($type == 'category') {
			$category = $categoryModel->load($id);
			$list     = $category->getSelectedPostsCollection();
		} elseif ($type == 'tag') {
			$tag  = $tagModel->load($id);
			$list = $tag->getSelectedPostsCollection();
		} elseif ($type == 'topic') {
			$topic = $topicModel->load($id);
			$list  = $topic->getSelectedPostsCollection();
		}

		if (count($list))
			return $list->addFieldToFilter('enabled', 1);

		return $posts;
	}

	/**
	 * @return $this
	 */
	public function getCategoryList()
	{
		$category = $this->categoryfactory->create();
		$list     = $category->getCollection()
			->addFieldToFilter('enabled', 1);

		return $list;
	}

	/**
	 * @return $this
	 */
	public function getTagList()
	{
		$tag  = $this->tagfactory->create();
		$list = $tag->getCollection()
			->addFieldToFilter('enabled', 1);

		return $list;
	}

	/**
	 * @param $array
	 * @return $this
	 */
	public function getCategoryCollection($array)
	{
		$category = $this->categoryfactory->create();
		$list     = $category->getCollection()
			->addFieldToFilter('enabled', 1)
			->addFieldToFilter('category_id', array('in' => $array));

		return $list;
	}

	/**
	 * @param $post
	 * @return string
	 */
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

	/**
	 * @param $code
	 * @return string
	 */
	public function getBlogUrl($code)
	{
		return $this->_getUrl($this->getBlogConfig('general/url_prefix') . '/' . $code);
	}

	/**
	 * @param $url
	 * @return $this
	 */
	public function getPostByUrl($url)
	{
		$url   = $this->checkSuffix($url);
		$posts = $this->postfactory->create()->load($url, 'url_key');

		return $posts;
	}

	/**
	 * @param $url
	 * @return mixed
	 */
	public function checkSuffix($url)
	{
		$url_suffix = $this->getBlogConfig('general/url_suffix');
		if (strpos($url, $url_suffix)) {
			$url = str_replace($url_suffix, '', $url);
		}

		return $url;
	}

	/**
	 * @param $tag
	 * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
	 */
	public function getPostsByTag($tag)
	{
		$posts      = $this->postfactory->create();
		$collection = $posts->getCollection();

		return $collection;
	}

	/**
	 * @param $category
	 * @return bool
	 */
	public function getPostsByCategory($category)
	{
		$collection = true;

		return $collection;
	}

	/**
	 * @param $image
	 * @return string
	 */
	public function getImageUrl($image)
	{
		return $this->getBaseMediaUrl() . '/' . $image;
	}

	/**
	 * @return mixed
	 */
	public function getBaseMediaUrl()
	{
		return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}

	/**
	 * @param $category
	 * @return string
	 */
	public function getCategoryUrl($category)
	{
		return $this->_getUrl($this->getBlogConfig('general/url_prefix') . '/category/' . $category->getUrlKey());
	}

	/**
	 * @param $tag
	 * @return string
	 */
	public function getTagUrl($tag)
	{
		return $this->_getUrl($this->getBlogConfig('general/url_prefix') . '/tag/' . $tag->getUrlKey());
	}

	/**
	 * @param $topic
	 * @return string
	 */
	public function getTopicUrl($topic)
	{
		return $this->_getUrl($this->getBlogConfig('general/url_prefix') . '/topic/' . $topic->getUrlKey());
	}

	/**
	 * @param $post
	 * @return null
	 */
	public function getPostCategoryHtml($post)
	{

		$categories = $this->getCategoryCollection($post->getCategoryIds());

		if (!$categories->getSize()) return null;
		$categoryHtml = array();

		foreach ($categories as $_cat) {
			$categoryHtml[] = '<a href="' . $this->getCategoryUrl($_cat) . '">' . $_cat->getName() . '</a>';
		}

		$result = implode(', ', $categoryHtml);

		return $result;

	}

	/**
	 * @param $id
	 * @return $this
	 */
	public function getPost($id)
	{
		$post = $this->postfactory->create()->load($id);

		return $post;
	}

	/**
	 * @param $code
	 * @param $param
	 * @return $this
	 */
	public function getCategoryByParam($code, $param)
	{
		if ($code == 'id') {
			return $this->categoryfactory->create()->load($param);
		} else {
			return $this->categoryfactory->create()->load($param, $code);
		}

	}

	/**
	 * @param $code
	 * @param $param
	 * @return $this
	 */
	public function getTagByParam($code, $param)
	{
		if ($code == 'id') {
			return $this->tagfactory->create()->load($param);
		} else {
			return $this->tagfactory->create()->load($param, $code);
		}

	}

	/**
	 * @param $code
	 * @param $param
	 * @return $this
	 */
	public function getTopicByParam($code, $param)
	{
		if ($code == 'id') {
			return $this->topicfactory->create()->load($param);
		} else {
			return $this->topicfactory->create()->load($param, $code);
		}

	}

	/**
	 * @param $postId
	 * @return \Mageplaza\Blog\Model\ResourceModel\Category\Collection
	 */
	public function getCategoryByPost($postId)
	{
		$post = $this->postfactory->create()->load($postId);

		return $post->getSelectedCategoriesCollection();
	}

	/**
	 * @param $postId
	 * @return \Mageplaza\Blog\Model\ResourceModel\Tag\Collection
	 */
	public function getTagsByPost($postId)
	{
		$post = $this->postfactory->create()->load($postId);

		return $post->getSelectedTagsCollection();
	}

	/**
	 * @param $postId
	 * @return \Mageplaza\Blog\Model\ResourceModel\Topic\Collection
	 */
	public function getTopicByPost($postId)
	{
		$post = $this->postfactory->create()->load($postId);

		return $post->getSelectedTopicsCollection();
	}

	/**
	 * @return mixed
	 */
	public function getCurrentUrl(){
		$model=$this->objectManager->get('Magento\Framework\UrlInterface');
		return $model->getCurrentUrl();
	}

}