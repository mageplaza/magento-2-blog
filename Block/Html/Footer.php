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

namespace Mageplaza\Blog\Block\Html;

use Magento\Framework\View\Element\Html\Link;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Footer
 * @package Mageplaza\Blog\Block\Html
 */
class Footer extends Link
{
    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    public $helper;

    /**
     * @var string
     */
    protected $_template = 'Mageplaza_Blog::html\footer.phtml';

    /**
     * Footer constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageplaza\Blog\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    )
    {
        $this->helper = $helper;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getHref()
    {
        return $this->helper->getBlogUrl('');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if ($this->helper->getBlogConfig('general/name') == "") {
            return __("Blog");
        }

        return $this->helper->getBlogConfig('general/name');
    }

    /**
     * @return string
     */
    public function getHtmlSiteMapUrl()
    {
        $moduleRoute = $this->helper->getBlogConfig('general/url_prefix');
        if ($moduleRoute) {
            return $this->getBaseUrl() . $moduleRoute . '/sitemap/';
        }

        return $this->getBaseUrl() . 'blog/sitemap/';
    }
}
