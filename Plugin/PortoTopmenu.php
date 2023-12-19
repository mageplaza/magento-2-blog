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

namespace Mageplaza\Blog\Plugin;

use Mageplaza\Blog\Block\Category\Menu;
use Mageplaza\Blog\Helper\Data;

/**
 * Class PortoTopmenu
 * @package Mageplaza\Blog\Plugin
 */
class PortoTopmenu
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * PortoTopmenu constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Smartwave\Megamenu\Block\Topmenu $topmenu
     * @param $html
     *
     * @return string
     */
    public function afterGetMegamenuHtml(\Smartwave\Megamenu\Block\Topmenu $topmenu, $html)
    {
        if ($this->helper->isEnabled() && $this->helper->getBlogConfig('general/toplinks')) {
            $blogHtml = $topmenu->getLayout()->createBlock(Menu::class)
                ->setTemplate('Mageplaza_Blog::category/topPortoMenu.phtml')->toHtml();

            return $html . $blogHtml;
        }

        return $html;
    }
}
