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

namespace Mageplaza\Blog\Controller;

use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\Url;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Router
 * @package Mageplaza\Blog\Controller
 */
class Router implements RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    public $actionFactory;

    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    public $helper;

    /**
     * @var
     */
    protected $_request;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Mageplaza\Blog\Helper\Data $helper
     */
    public function __construct(
        ActionFactory $actionFactory,
        Data $helper
    )
    {
        $this->actionFactory = $actionFactory;
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
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        if (!$this->helper->isEnabled()) {
            return null;
        }

        $identifier = trim($request->getPathInfo(), '/');
        $urlSuffix  = $this->helper->getUrlSuffix();
        if ($length = strlen($urlSuffix)) {
            if (substr($identifier, -$length) == $urlSuffix) {
                $identifier = substr($identifier, 0, strlen($identifier) - $length);
            } else {
                return null;
            }
        }

        $routePath = explode('/', $identifier);
        $routeSize = sizeof($routePath);
        if (!$routeSize || ($routeSize > 3) || (array_shift($routePath) != $this->helper->getRoute())) {
            return null;
        }

        $request->setModuleName('mpblog')
            ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier . $urlSuffix);
        $controller = array_shift($routePath);
        if (!$controller) {
            $request->setControllerName('post')
                ->setActionName('index')
                ->setPathInfo('/mpblog/post/index');

            return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
        }

        $action = array_shift($routePath) ?: 'index';
        switch ($controller) {
            case 'post':
                if (!in_array($action, ['index', 'rss'])) {
                    $post   = $this->helper->getObjectByParam($action, 'url_key');
                    $request->setParam('id', $post->getId());
                    $action = 'view';
                }
                break;
            case 'category':
                if (!in_array($action, ['index', 'rss'])) {
                    $category = $this->helper->getObjectByParam($action, 'url_key', Data::TYPE_CATEGORY);
                    $request->setParam('id', $category->getId());
                    $action = 'view';
                }
                break;
            case 'tag':
                $tag = $this->helper->getObjectByParam($action, 'url_key', Data::TYPE_TAG);
                $request->setParam('id', $tag->getId());
                $action = 'view';
                break;
            case 'topic':
                $topic = $this->helper->getObjectByParam($action, 'url_key', Data::TYPE_TOPIC);
                $request->setParam('id', $topic->getId());
                $action = 'view';
                break;
            case 'sitemap':
                $action = 'index';
                break;
            case 'author':
                $author = $this->helper->getObjectByParam($action, 'url_key', Data::TYPE_AUTHOR);
                $request->setParam('id', $author->getId());
                $action = 'view';
                break;
            case 'month':
                $request->setParam('month_key', $action);
                $action = 'view';
                break;
            default:
                $post = $this->helper->getObjectByParam($controller, 'url_key');
                $request->setParam('id', $post->getId());
                $controller = 'post';
                $action     = 'view';
        }

        $request->setControllerName($controller)
            ->setActionName($action)
            ->setPathInfo('/mpblog/' . $controller . '/' . $action);

        return $this->actionFactory->create('Magento\Framework\App\Action\Forward');
    }
}
