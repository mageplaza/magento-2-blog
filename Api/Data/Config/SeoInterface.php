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
 * Interface SeoInterface
 * @package Mageplaza\Blog\Api\Data\Config
 */
interface SeoInterface
{
    const META_TITLE       = 'meta_title';
    const META_DESCRIPTION = 'meta_description';

    /**
     * @return string/null
     */
    public function getMetaTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMetaTitle($value);

    /**
     * @return string/null
     */
    public function getMetaDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMetaDescription($value);
}
