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

namespace Mageplaza\Blog\Helper;

use Mageplaza\Core\Helper\Media;

/**
 * Class Image
 * @package Mageplaza\Blog\Helper
 */
class Image extends Media
{
    const TEMPLATE_MEDIA_PATH = 'mageplaza/blog';
    const TEMPLATE_MEDIA_TYPE_AUTH = 'auth';
    const TEMPLATE_MEDIA_TYPE_POST = 'post';
}
