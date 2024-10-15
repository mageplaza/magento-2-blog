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

namespace Mageplaza\Blog\Plugin;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Block\Category\Menu;
use Mageplaza\Blog\Helper\Data;
use Magento\Framework\View\Element\Template;
/**
 * Class Topmenu
 * @package Mageplaza\Blog\Plugin
 */
class HyvaMenu
{
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * Topmenu constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->helper = $helper;
        $this->layout = $layout;
    }

    /**
     * @param /Hyva\Theme\ViewModel\Navigation $subject
     * @param $data
     *
     * @return string
     * @throws LocalizedException
     */
    public function afterGetNavigation(
        \Hyva\Theme\ViewModel\Navigation $subject,
        array $dataSubject = []
    ) {
        if ($this->helper->isEnabled() && $this->helper->getBlogConfig('display/toplinks')) {
            $blockMenu  = $this->layout->createBlock(Menu::class);
            $categories = $blockMenu->getCollections();
            $childData  = [];
            foreach ($categories as $key => $category) {
                $data              = [
                    "name"             => $category->getName(),
                    "id"               => "mg-blog" . $category->getId(),
                    "url"              => $blockMenu->getBlogUrlByUrlKey($category->getUrlKey()),
                    "image"            => false,
                    "has_active"       => false,
                    "is_active"        => false,
                    "is_category"      => true,
                    "is_parent_active" => true,
                    "position"         => null,
                    "path"             => $category->getPath(),
                    "childData"        => $blockMenu->getChildDataCate($category)
                ];
                array_push($childData, $data);
            };

            $dataSubject['mg-blog']= [
                "name"             => $blockMenu->getBlogHomePageTitle(),
                "id"               => "mg-blog",
                "url"              => $blockMenu->getBlogHomeUrl(),
                "image"            => false,
                "has_active"       => false,
                "is_active"        => false,
                "is_category"      => true,
                "is_parent_active" => true,
                "position"         => null,
                "path"             => $blockMenu->getPath(),
                "childData"        => $childData
            ];
        }


        return $dataSubject;
    }
}
