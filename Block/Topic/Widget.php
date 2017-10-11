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

namespace Mageplaza\Blog\Block\Topic;

use Mageplaza\Blog\Block\Frontend;

/**
 * Class Widget
 * @package Mageplaza\Blog\Block\Topic
 */
class Widget extends Frontend
{
    /**
     * @return array|string
     */
    public function getTopicList()
    {
        return $this->helperData->getTopicList();
    }

    /**
     * @param $topic
     * @return string
     */
    public function getTopicUrl($topic)
    {
        return $this->helperData->getTopicUrl($topic);
    }
}
