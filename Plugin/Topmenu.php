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
namespace Mageplaza\Blog\Plugin;

class Topmenu
{
    public $helper;

    public function __construct(
        \Mageplaza\Blog\Helper\Data $helper
    ) {
    
        $this->helper = $helper;
    }

    public function afterGetHtml(\Magento\Theme\Block\Html\Topmenu $topmenu, $html)
    {
        $blogHtml='';
    	if ($this->helper->getBlogConfig('general/toplinks') && $this->helper->getBlogConfig('general/enabled')){

            $blogHtml = $topmenu->getLayout()->createBlock('Mageplaza\Blog\Block\Html\CategoryMenu')->toHtml();
		}
		return $html . $blogHtml;

    }
}
