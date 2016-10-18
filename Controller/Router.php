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
    protected $actionFactory;

    /**
     * Event manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;

    /**
     * Config primary
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Url
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
    /**
     * Helper
     *
     * @var \Mageplaza\Blog\Helper\Data
     */
    protected $_helper;

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
        $this->_eventManager = $eventManager;
        $this->_url          = $url;
        $this->_pageFactory  = $pageFactory;
        $this->_storeManager = $storeManager;
        $this->_response     = $response;
        $this->_helper       = $helper;
    }

    /**
     * Validate and Match Cms Page and modify request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return bool
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $helper = $this->_helper;
        if ($helper->getBlogConfig('general/enabled')) {
            $url_prefix = $helper->getBlogConfig('general/url_prefix');
            $url_suffix = $helper->getBlogConfig('general/url_suffix');
            if ($url_prefix == '') {
                return $this;
            }
            $path = trim($request->getPathInfo(), '/');

            if (strpos($path, $url_prefix) === 0) {
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

                    if ($type == 'post' && $array[2] == 'index') {
                        if ($array[2] == 'index') {
                            $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                            $request->setPathInfo('/' . 'blog/post/index');

                            return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                        } else {
                            $url_key = $array[2];
                            $post    = $this->_helper->getPostByUrl($url_key);
                            if ($post && $post->getId()) {
                                $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $url_key);
                                $request->setPathInfo('/' . 'blog/post/view/id/' . $post->getId());

                                return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                            }
                        }
                    }
                    if ($type == 'post' && strpos($path, 'rss') !== false) {
                        $path = str_replace($url_prefix, 'blog', $path);
                        $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                        $request->setPathInfo($path);

                        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                    }
                    if ($type == 'topic') {
                        $topicUrlKey = $array[2];
                        $topic       = $this->_helper->getTopicByParam('url_key', $topicUrlKey);
                        $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                        $request->setPathInfo('/' . 'blog/topic/view/id/' . $topic->getId());

                        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                    }
                    if ($type == 'tag') {
                        $tagUrlKey = $array[2];
                        $tag       = $this->_helper->getTagByParam('url_key', $tagUrlKey);
                        $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                        $request->setPathInfo('/' . 'blog/tag/view/id/' . $tag->getId());

                        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                    }
                    if ($type == 'category') {
                        $categoryName = $array[2];
                        $category     = $this->_helper->getCategoryByParam('url_key', $categoryName);
                        if ($category && $category->getId()) {
                            $request->setAlias(\Magento\Framework\UrlInterface::REWRITE_REQUEST_PATH_ALIAS, $path);
                            $request->setPathInfo('/' . 'blog/category/view/id/' . $category->getId());

                            return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
                        }
                    }
                } elseif (count($array) > 3) {
                    if (strpos($path, 'rss') !== false) {
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
