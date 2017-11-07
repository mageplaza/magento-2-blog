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

namespace Mageplaza\Blog\Block\Tag;

use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Widget
 * @package Mageplaza\Blog\Block\Tag
 */
class Widget extends Frontend
{
    /**
     * @var \Mageplaza\Blog\Model\ResourceModel\Tag\Collection
     */
    protected $_tagList;

    /**
     * @return array|string
     */
    public function getTagList()
    {
        if (!$this->_tagList) {
            $this->_tagList = $this->helperData->getObjectList(Data::TYPE_TAG);
        }

        return $this->_tagList;
    }

    /**
     * @param $tag
     * @return string
     */
    public function getTagUrl($tag)
    {
        return $this->helperData->getBlogUrl($tag, Data::TYPE_TAG);
    }

    /**
     * get tags size based on num of post
     *
     * @param $tag
     * @return float|string
     */
    public function getTagSize($tag)
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $postList */
        $postList = $this->helperData->getPostList();
        if ($postList && ($max = $postList->getSize()) > 1) {
            $maxSize = 22;
            $tagPost = $this->helperData->getPostCollection(Data::TYPE_TAG, $tag->getId());
            if ($tagPost && ($countTagPost = $tagPost->getSize()) > 1) {
                $size = $maxSize * $countTagPost / $max;

                return round($size) + 8;
            }
        }

        return 8;
    }
}
