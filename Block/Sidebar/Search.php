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
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Sidebar;

use Mageplaza\Blog\Block\Frontend;

/**
 * Class Search
 * @package Mageplaza\Blog\Block\Sidebar
 */
class Search extends Frontend
{
    /**
     * @return string
     */
    public function getSearchBlogData()
    {
        $result    = [];
        $posts     = $this->helperData->getPostList();
        $limitDesc = $this->getSidebarConfig('search/description') ?: 100;
        if (!empty($posts)) {
            foreach ($posts as $item) {
                $tmp = [
                    'value' => $item->getName(),
                    'url'   => $this->getUrlByPost($item),
                    'image' => $item->getImage() ? $this->getImageUrl($item->getImage()) : $this->getDefaultImageUrl(),
                    'desc'  => $item->getShortDescription() ? substr($item->getShortDescription(), 0, $limitDesc)
                        : 'No description'
                ];
                array_push($result, $tmp);
            }
        }

        return json_encode($result);
    }
}
