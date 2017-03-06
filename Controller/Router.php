<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mageplaza\Blog\Controller;

/**
 * Cms Controller Router
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
	public $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
	public $eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
	public $storeManager;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
	public $pageFactory;

    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
	public $appState;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
	public $url;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
	public $response;
    /**
     * Helper
     *
     * @var \Mageplaza\Blog\Helper\Data
     */
	public $helper;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Cms\Model\PageFactory $pageFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\UrlInterface $url,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\ResponseInterface $response,
        \Mageplaza\Blog\Helper\Data $helper
    ) {
    
        $this->actionFactory = $actionFactory;
        $this->eventManager = $eventManager;
        $this->url          = $url;
        $this->pageFactory  = $pageFactory;
        $this->storeManager = $storeManager;
        $this->response     = $response;
        $this->helper       = $helper;
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $helper = $this->helper;
        if ($helper->getBlogConfig('general/enabled')) {
            $url_prefix = $helper->getBlogConfig('general/url_prefix');
            if ($url_prefix == '') {
                return $this;
            }
            $path = trim($request->getPathInfo(), '/');

            if (strpos($path, $url_prefix) !== false) {
                $array = explode('/', $path);

                if (count($array) == 1) {
                    $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                    $request->setPathInfo('/' . 'blog/post/index');

                    return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                } elseif (count($array) == 2) {
                    $url_key = $array[1];
                    $post    = $this->_helper->getPostByUrl($url_key);
                    if ($post && $post->getId()) {
                        $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $url_key);
                        $request->setPathInfo('/' . 'blog/post/view/id/' . $post->getId());

                        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                    }
                } elseif (count($array) == 3) {
                    $type = $array[1];

                    if ($type == 'post') {
                        if ($array[2] == 'index') {
                            $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                            $request->setPathInfo('/' . 'blog/post/index');

                            return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                        } else {
                            $url_key = $array[2];
                            $post    = $this->helper->getPostByUrl($url_key);
                            if ($post && $post->getId()) {
                                $request->setAlias(
                                	\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS,
									$url_key
								);
                                $request->setPathInfo('/' . 'blog/post/view/id/' . $post->getId());

                                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                            }
                        }
                    }

                    $hasRss = is_numeric(strpos($path, 'rss'));
                    if ($type == 'post' && $hasRss) {
                        $path = str_replace($url_prefix, 'blog', $path);
                        $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                        $request->setPathInfo($path);

                        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                    }
                    if ($type == 'topic') {
                        $topicUrlKey = $array[2];
                        $topic       = $this->helper->getTopicByParam('url_key', $topicUrlKey);
                        $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                        $request->setPathInfo('/' . 'blog/topic/view/id/' . $topic->getId());

                        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                    }
                    if ($type == 'tag') {
                        $tagUrlKey = $array[2];
                        $tag       = $this->helper->getTagByParam('url_key', $tagUrlKey);
                        $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                        $request->setPathInfo('/' . 'blog/tag/view/id/' . $tag->getId());

                        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                    }
                    if ($type == 'category') {
                        $categoryName = $array[2];
                        $category     = $this->helper->getCategoryByParam('url_key', $categoryName);
                        if ($category && $category->getId()) {
                            $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                            $request->setPathInfo('/' . 'blog/category/view/id/' . $category->getId());

                            return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                        }
                    }
                } elseif (count($array) > 3) {
                    $hasRss = is_numeric(strpos($path, 'rss'));
                    if ($hasRss) {
                        $path = str_replace($url_prefix, 'blog', $path);
                        $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                        $request->setPathInfo($path);

                        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                    }
                }
            }
        }
    }
}
