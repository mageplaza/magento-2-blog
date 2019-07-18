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

use Infortis\UltraMegamenu\Block\Navigation;

/**
 * Class InfortisTopmenu
 * @package Mageplaza\Blog\Plugin
 */
class InfortisTopmenu
{
    /**
     * @param Navigation $topmenu
     * @param $html
     *
     * @return string
     */
    public function afterRenderCategoriesMenuHtml(Navigation $topmenu, $html)
    {
        $html .= $topmenu->getLayout()
            ->createBlock(\Mageplaza\Blog\Block\Frontend::class)
            ->setTemplate('Mageplaza_Blog::position/topmenuinfortis.phtml')
            ->toHtml();

        return $html;
    }
}
