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

namespace Mageplaza\Blog\Block\Tag;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;
use Mageplaza\Blog\Model\ResourceModel\Author\Collection as AuthorCollection;
use Mageplaza\Blog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Mageplaza\Blog\Model\ResourceModel\Tag\Collection as TagCollection;
use Mageplaza\Blog\Model\ResourceModel\Topic\Collection as TopicCollection;
use Mageplaza\Blog\Model\Tag;

/**
 * Class Widget
 * @package Mageplaza\Blog\Block\Tag
 */
class Widget extends Frontend
{
    /**
     * @var TagCollection
     */
    protected $_tagList;

    /**
     * @return AuthorCollection|CategoryCollection|Collection|TagCollection|TopicCollection|null
     */
    public function getTagList()
    {
        try {
            if (!$this->_tagList) {
                $this->_tagList = $this->helperData->getObjectList(Data::TYPE_TAG);
            }

            return $this->_tagList;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * @param Tag $tag
     *
     * @return string
     */
    public function getTagUrl($tag)
    {
        return $this->helperData->getBlogUrl($tag, Data::TYPE_TAG);
    }

    /**
     * Get tags size based on num of post
     *
     * @param $tag
     *
     * @return false|float|int
     * @throws NoSuchEntityException
     */
    public function getTagSize($tag)
    {
        /** @var Collection $postList */
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
