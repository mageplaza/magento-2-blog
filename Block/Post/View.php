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
namespace Mageplaza\Blog\Block\Post;

use Mageplaza\Blog\Block\Frontend;

/**
 * Class View
 * @package Mageplaza\Blog\Block\Post
 */
class View extends Frontend
{
	/**
	 * config logo blog path
	 */
    const LOGO = 'mageplaza/blog/logo/';

	/**
	 * @return string
	 */
    public function checkRss()
    {
        return $this->helperData->getBlogUrl('post/rss');
    }

	/**
	 * @param $topic
	 * @return string
	 */
    public function getTopicUrl($topic)
    {
        return $this->helperData->getTopicUrl($topic);
    }

	/**
	 * @param $tag
	 * @return string
	 */
    public function getTagUrl($tag)
    {
        return $this->helperData->getTagUrl($tag);
    }

	/**
	 * @param $category
	 * @return string
	 */
    public function getCategoryUrl($category)
    {
        return $this->helperData->getCategoryUrl($category);
    }

	/**
	 * @return bool|mixed
	 */
    public function checkComment()
    {
        if (!$this->helperData->getBlogConfig('general/enabled')) {
            return false;
        }
        $comment = $this->helperData->getBlogConfig('comment/type');

        return $comment;
    }

	/**
	 * @param $code
	 * @return mixed
	 */
    public function helperComment($code)
    {
        return $this->helperData->getBlogConfig('comment/' . $code);
    }

    /**
     * get tag list
     * @param $post
     * @return string
     */
    public function getTagList($post)
    {
        $tagCollection = $post->getSelectedTagsCollection();
        $result        = '';
        if (!empty($tagCollection)) :
            $listTags = [];
            foreach ($tagCollection as $tag) {
                $listTags[] = '<a class="mp-info" href="' . $this->getTagUrl($tag) . '">' . $tag->getName() . '</a>';
            }
            $result = implode(', ', $listTags);
        endif;

        return $result;
    }

	/**
	 * @param $image
	 * @return string
	 */
    public function getLogoImage($image)
    {
        return $this->helperData->getBaseMediaUrl() . self::LOGO . $image;
    }

	/**
	 * @param $content
	 * @return string
	 */
    public function getPageFilter($content)
    {
        return $this->filterProvider->getPageFilter()->filter($content);
    }
}
