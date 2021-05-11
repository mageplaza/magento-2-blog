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
 * Interface MonthlyArchiveInterface
 * @package Mageplaza\Blog\Api\Data
 */
interface MonthlyArchiveInterface
{
    const LABEL      = 'label';
    const POST_COUNT = 'post_count';
    const LINK       = 'link';

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setLabel($value);

    /**
     * @return int
     */
    public function getPostCount();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setPostCount($value);

    /**
     * @return string
     */
    public function getLink();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setLink($value);
}
