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
namespace Mageplaza\Blog\Block\Category;

use Mageplaza\Blog\Block\Frontend;

class Listpost extends Frontend
{
    public function getPostList()
    {
        return $this->getBlogPagination('category', $this->getRequest()->getParam('id'));
    }

    public function checkRss()
    {
        $categoryId = $this->getRequest()->getParam('id');
        if (!$categoryId) {
            return false;
        }

        return $this->helperData->getBlogUrl('category/rss/category_id/' . $categoryId);
    }
}
