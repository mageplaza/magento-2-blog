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
namespace Mageplaza\Blog\Controller;


/**
 * Class Router
 * @package Mageplaza\Blog\Controller
 */
class Router implements \Magento\Framework\App\RouterInterface
{
	/**
	 * @var \Magento\Framework\App\ActionFactory
	 */
	public $actionFactory;

	/**
	 * @var \Mageplaza\Blog\Helper\Data
	 */
	public $helper;

	protected $_request;

	protected $block;

	/**
	 * @param \Magento\Framework\App\ActionFactory $actionFactory
	 * @param \Mageplaza\Blog\Helper\Data $helper
	 */
	public function __construct(
		\Magento\Framework\App\ActionFactory $actionFactory,
		\Mageplaza\Blog\Block\Frontend $block,
		\Mageplaza\Blog\Helper\Data $helper
	)
	{
		$this->actionFactory = $actionFactory;
		$this->block=$block;
		$this->helper        = $helper;
	}

	/**
	 * @param $controller
	 * @param $action
	 * @param array $params
	 * @return \Magento\Framework\App\ActionInterface
	 */
	public function _forward($controller, $action, $params = [])
	{
		$this->_request->setControllerName($controller)
			->setActionName($action)
			->setPathInfo('/mpblog/' . $controller . '/' . $action);

		foreach ($params as $key => $value) {
			$this->_request->setParam($key, $value);
		}

		return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
	}

	/**
	 * Validate and Match Cms Page and modify request
	 *
	 * @param \Magento\Framework\App\RequestInterface $request
	 * @return bool
	 */
	public function match(\Magento\Framework\App\RequestInterface $request)
	{
		$posts=$this->block->getBlogPagination();
		$count=$posts[0];
		$pageParams=$request->getParams();
		$pageParam=$request->getParam('p');
		if($pageParams) {
			if (count($pageParams) > 1 || $pageParam > $count || $pageParam <= 0 )
			{
				return null;
			}
		}
		$identifier = trim($request->getPathInfo(), '/');
		$routePath  = explode('/', $identifier);
		$routeSize  = sizeof($routePath);
		if (!$this->helper->isEnabled() ||
			!$routeSize || ($routeSize > 3) ||
			(array_shift($routePath) != $this->helper->getBlogConfig('general/url_prefix'))
		) {
			return null;
		}

		$request->setModuleName('mpblog')
			->setAlias(\Magento\Framework\Url::REWRITE_REQUEST_PATH_ALIAS, $identifier);

		$this->_request = $request;
		$params     = [];
		$controller = array_shift($routePath);

		if (!$controller) {
			return $this->_forward('post', 'index');
		}

		switch ($controller) {
			case 'post':
				$path = array_shift($routePath);
				$action = $path ?: 'index';

				if (!in_array($action, ['index', 'rss'])) {
					$post = $this->helper->getPostByUrl($action);

					$action = 'view';
					$params = ['id' => $post->getId()];
				}

				break;
			case 'category':
				$path = array_shift($routePath);
				$action = $path ?: 'index';

				if (!in_array($action, ['index', 'rss'])) {
					$category = $this->helper->getCategoryByParam('url_key', $action);

					$action = 'view';
					$params = ['id' => $category->getId()];
				}

				break;
			case 'tag':
				$path = array_shift($routePath);
				$tag    = $this->helper->getTagByParam('url_key', $path);

				$action = 'view';
				$params = ['id' => $tag->getId()];

				break;
			case 'topic':
				$path = array_shift($routePath);
				$topic  = $this->helper->getTopicByParam('url_key', $path);

				$action = 'view';
				$params = ['id' => $topic->getId()];

				break;
			default:
				$post = $this->helper->getPostByUrl($controller);

				$controller = 'post';
				$action     = 'view';
				$params     = ['id' => $post->getId()];
		}

		return $this->_forward($controller, $action, $params);
	}
}
