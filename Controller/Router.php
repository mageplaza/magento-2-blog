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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Controller;

use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\ActionFactory;
use Magento\Framework\App\ActionInterface;
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
    const URL_SUFFIX_RSS_XML = '.xml';

    /**
     * @var ActionFactory
     */
    public $actionFactory;

    /**
     * @var Data
     */
    public $helper;

    /**
     * @param ActionFactory $actionFactory
     * @param Data $helper
     */
    public function __construct(
        ActionFactory $actionFactory,
        Data $helper
    ) {
        $this->actionFactory = $actionFactory;
        $this->helper = $helper;
    }

    /**
     * @param RequestInterface $request
     *
     * @return ActionInterface|null
     */
    public function match(RequestInterface $request)
    {
        if (!$this->helper->isEnabled()) {
            return null;
        }

        $rssAction = "rss.xml";
        $identifier = trim($request->getPathInfo(), '/');
        $urlSuffix = $this->helper->getUrlSuffix();

        if ($length = strlen($urlSuffix)) {
            if (substr($identifier, -$length) === $urlSuffix && !$this->isRss($identifier)) {
                $identifier = substr($identifier, 0, strlen($identifier) - $length);
            } else {
                $identifier = $this->checkRssIdentifier($identifier);
            }
        } elseif (strpos($identifier, $rssAction) !== false) {
            $identifier = $this->checkRssIdentifier($identifier);
        }

        $routePath = explode('/', $identifier);
        $routeSize = count($routePath);
        if (!$routeSize || ($routeSize > 3) || (array_shift($routePath) !== $this->helper->getRoute())) {
            return null;
        }

        $request->setModuleName('mpblog')
            ->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $identifier . $urlSuffix);
        $controller = array_shift($routePath);
        if (!$controller) {
            $request->setControllerName('post')
                ->setActionName('index')
                ->setPathInfo('/mpblog/post/index');

            return $this->actionFactory->create(Forward::class);
        }

        $action = array_shift($routePath) ?: 'index';

        switch ($controller) {
            case 'post':
                if (!in_array($action, ['index', 'rss'])) {
                    $post = $this->helper->getObjectByParam($action, 'url_key');
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
                $action = 'view';
        }

        $request->setControllerName($controller)
            ->setActionName($action)
            ->setPathInfo('/mpblog/' . $controller . '/' . $action);

        return $this->actionFactory->create(Forward::class);
    }

    /**
     * check if action = rss
     *
     * @param $identifier
     *
     * @return bool
     */
    public function isRss($identifier)
    {
        $routePath = explode('/', $identifier);
        $routePath = array_pop($routePath);
        $routePath = explode('.', $routePath);
        $action = array_shift($routePath);

        return $action === 'rss';
    }

    /**
     * @param $identifier
     *
     * @return bool|null|string
     */
    public function checkRssIdentifier($identifier)
    {
        $length = strlen(self::URL_SUFFIX_RSS_XML);
        if (substr($identifier, -$length) == self::URL_SUFFIX_RSS_XML && $this->isRss($identifier)) {
            $identifier = substr($identifier, 0, strlen($identifier) - $length);

            return $identifier;
        }

        return null;
    }
}
