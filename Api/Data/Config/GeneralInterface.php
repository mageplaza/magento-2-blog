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
 * Interface GeneralInterface
 * @package Mageplaza\Blog\Api\Data\Config
 */
interface GeneralInterface
{
    const BLOG_NAME           = 'blog_name';
    const IS_LINK_IN_MENU   = 'is_link_in_menu';
    const IS_DISPLAY_AUTHOR = 'is_display_author';
    const BLOG_MODE         = 'blog_mode';
    const BLOG_COLOR        = 'blog_color';

    /**
     * @return string/null
     */
    public function getBlogName();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBlogName($value);

    /**
     * @return boolean/null
     */
    public function getIsLinkInMenu();

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setIsLinkInMenu($value);

    /**
     * @return boolean/null
     */
    public function getIsDisplayAuthor();

    /**
     * @param boolean $value
     *
     * @return $this
     */
    public function setIsDisplayAuthor($value);

    /**
     * @return string/null
     */
    public function getBlogMode();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBlogMode($value);

    /**
     * @return string/null
     */
    public function getBlogColor();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setBlogColor($value);
}
