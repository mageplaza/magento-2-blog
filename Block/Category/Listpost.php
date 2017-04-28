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
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Blog\Block\Category;

use Mageplaza\Blog\Block\Frontend;

class Listpost extends Frontend
{
    public function getPostList()
    {
        return $this->getBlogPagination(\Mageplaza\Blog\Helper\Data::CATEGORY, $this->getRequest()->getParam('id'));
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
