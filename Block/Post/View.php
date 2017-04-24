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

class View extends Frontend
{
	const LOGO = 'mageplaza/blog/logo/';

	public function checkRss()
	{
		return $this->helperData->getBlogUrl('post/rss');
	}
    public function getTopicUrl($topic)
    {
        return $this->helperData->getTopicUrl($topic);
    }

    public function getTagUrl($tag)
    {
        return $this->helperData->getTagUrl($tag);
    }

    public function getCategoryUrl($category)
    {
        return $this->helperData->getCategoryUrl($category);
    }

    public function checkComment()
    {
        if (!$this->helperData->getBlogConfig('general/enabled')) {
            return false;
        }
        $comment = $this->helperData->getBlogConfig('comment/type');

        return $comment;
    }

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
	 * get Logo for seo article snippet
	 */
    public function getLogoImage($image)
	{
		return $this->helperData->getBaseMediaUrl() . self::LOGO . $image;
	}

	public function getPageFilter($content)
	{
		return $this->filterProvider->getPageFilter()->filter($content);
	}

}
