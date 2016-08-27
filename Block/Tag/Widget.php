<?php
/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 * @category  Mageplaza
 * @package   Mageplaza_Blog
 * @copyright Copyright (c) 2016
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Block\Tag;

use Mageplaza\Blog\Block\Frontend;

class Widget extends Frontend
{
    public function getTagList()
    {

        return $this->helperData->getTagList();
    }

    public function getTagUrl($tag)
    {
        return $this->helperData->getTagUrl($tag);
    }
}
