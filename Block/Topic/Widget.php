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

namespace Mageplaza\Blog\Block\Topic;

use Exception;
use Mageplaza\Blog\Model\ResourceModel\Author\Collection as AuthorCollection;
use Mageplaza\Blog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;
use Mageplaza\Blog\Model\ResourceModel\Tag\Collection as TagCollection;
use Mageplaza\Blog\Model\ResourceModel\Topic\Collection as TopicCollection;
use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\Topic;

/**
 * Class Widget
 * @package Mageplaza\Blog\Block\Topic
 */
class Widget extends Frontend
{
    /**
     * @return AuthorCollection|CategoryCollection|Collection|TagCollection|TopicCollection|null
     */
    public function getTopicList()
    {
        try {
            return $this->helperData->getObjectList(Data::TYPE_TOPIC);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param Topic $topic
     *
     * @return string
     */
    public function getTopicUrl($topic)
    {
        return $this->helperData->getBlogUrl($topic, Data::TYPE_TOPIC);
    }
}
