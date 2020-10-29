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

namespace Mageplaza\Blog\Api\Data\Config;

/**
 * Interface SidebarInterface
 * @package Mageplaza\Blog\Api\Data\Config
 */
interface SidebarInterface
{
    const NUMBER_RECENT    = 'number_recent';
    const NUMBER_MOST_VIEW = 'number_most_view';

    /**
     * @return string/null
     */
    public function getNumberRecent();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setNumberRecent($value);

    /**
     * @return string/null
     */
    public function getNumberMostView();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setNumberMostView($value);
}
