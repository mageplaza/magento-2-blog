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

namespace Mageplaza\Blog\Api\Data;

/**
 * Interface BlogConfigInterface
 * @package Mageplaza\Blog\Api\Data
 */
interface BlogConfigInterface
{
    const GENERAL = 'general';
    const SIDEBAR = 'sidebar';
    const SEO     = 'seo';

    /**
     * @return \Mageplaza\Blog\Api\Data\Config\GeneralInterface
     */
    public function getGeneral();

    /**
     * @param \Mageplaza\Blog\Api\Data\Config\GeneralInterface $value
     *
     * @return $this
     */
    public function setGeneral($value);

    /**
     * @return \Mageplaza\Blog\Api\Data\Config\SidebarInterface
     */
    public function getSidebar();

    /**
     * @param \Mageplaza\Blog\Api\Data\Config\SidebarInterface $value
     *
     * @return $this
     */
    public function setSidebar($value);

    /**
     * @return \Mageplaza\Blog\Api\Data\Config\SeoInterface
     */
    public function getSeo();

    /**
     * @param \Mageplaza\Blog\Api\Data\Config\SeoInterface $value
     *
     * @return $this
     */
    public function setSeo($value);
}
