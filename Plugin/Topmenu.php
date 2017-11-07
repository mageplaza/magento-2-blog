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

namespace Mageplaza\Blog\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Tree\Node;
use Magento\Framework\Data\TreeFactory;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Topmenu
 * @package Mageplaza\Blog\Plugin
 */
class Topmenu
{
    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Data\TreeFactory
     */
    protected $treeFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * Topmenu constructor.
     * @param \Mageplaza\Blog\Helper\Data $helper
     * @param \Magento\Framework\Data\TreeFactory $treeFactory
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        Data $helper,
        TreeFactory $treeFactory,
        RequestInterface $request
    )
    {
        $this->helper      = $helper;
        $this->treeFactory = $treeFactory;
        $this->request     = $request;
    }

    /**
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param string $outermostClass
     * @param string $childrenWrapClass
     * @param int $limit
     */
    public function beforeGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $outermostClass = '',
        $childrenWrapClass = '',
        $limit = 0
    )
    {
        if ($this->helper->isEnabled() && $this->helper->getBlogConfig('general/toplinks')) {
            $subject->getMenu()
                ->addChild(
                    new Node(
                        $this->getMenuAsArray(),
                        'id',
                        $this->treeFactory->create()
                    )
                );
        }
    }

    /**
     * @return array
     */
    private function getMenuAsArray()
    {
        $identifier = trim($this->request->getPathInfo(), '/');
        $routePath  = explode('/', $identifier);
        $routeSize  = sizeof($routePath);

        return [
            'name'       => $this->helper->getBlogConfig('general/name') ?: __('Blog'),
            'id'         => 'mpblog-node',
            'url'        => $this->helper->getBlogUrl(''),
            'has_active' => ($identifier == 'mpblog/post/index'),
            'is_active'  => ('mpblog' == array_shift($routePath)) && ($routeSize == 3)
        ];
    }
}
