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

namespace Mageplaza\Blog\Block\Post;

use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;

/**
 * Class ManagePost
 * @package Mageplaza\Blog\Block\Post
 */
class ManagePost extends Frontend
{
    /**
     * @return string
     */
    public function getCategoriesTree()
    {
        return Data::jsonEncode($this->categoryOptions->getCategoriesTree());
    }

    /**
     * @return string
     */
    public function getTopicTree()
    {
        return Data::jsonEncode($this->topicOptions->getTopicsCollection());
    }

    /**
     * @return string
     */
    public function getTagTree()
    {
        return Data::jsonEncode($this->tagOptions->getTagsCollection());
    }

    /**
     * @return bool
     */
    public function checkTheme()
    {
        return $this->themeProvider->getThemeById($this->helperData->getCurrentThemeId())
                ->getCode() === 'Smartwave/porto';
    }
}
