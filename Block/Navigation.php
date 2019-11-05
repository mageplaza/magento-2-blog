<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\Blog\Block;

use Magento\Framework\View\Element\Html\Links;

/**
 * Class Navigation
 * @package Mageplaza\Blog\Block
 */
class Navigation extends Links
{
    /**
     * {@inheritdoc}
     */
    public function getLinks()
    {
        $links = parent::getLinks();

        usort($links, [$this, "compare"]);

        return $links;
    }

    /**
     * @param $firstLink
     * @param $secondLink
     *
     * @return bool
     */
    private function compare($firstLink, $secondLink)
    {
        return ($firstLink->getData('sortOrder') > $secondLink->getData('sortOrder'));
    }
}
