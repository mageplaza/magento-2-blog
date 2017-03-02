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

class Listpost extends Frontend
{

    public function getPostList()
    {
        return $this->helperData->getPostList('tag', $this->getRequest()->getParam('id'));
    }
    public function checkRss()
    {
        return $this->helperData->getBlogUrl('post/rss');
    }
}
