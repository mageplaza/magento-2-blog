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

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Block\Category\Menu;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Topmenu
 * @package Mageplaza\Blog\Plugin
 */
class Topmenu
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Topmenu constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Theme\Block\Html\Topmenu $subject
     * @param $html
     *
     * @return string
     * @throws LocalizedException
     */
    public function afterGetHtml(
        \Magento\Theme\Block\Html\Topmenu $subject,
        $html
    ) {
        if ($this->helper->isEnabled() && $this->helper->getBlogConfig('general/toplinks')) {
            $blogHtml = $subject->getLayout()->createBlock(Menu::class)
                ->setTemplate('Mageplaza_Blog::category/topmenu.phtml')->toHtml();

            return $html . $blogHtml;
        }

        return $html;
    }
}
