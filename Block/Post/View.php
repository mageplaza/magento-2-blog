<?php
/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 * @category  Mageplaza
 * @package   Mageplaza_Blog
 * @copyright Copyright (c) 2016
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Block\Post;

use Mageplaza\Blog\Block\Frontend;

class View extends Frontend
{

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
        if (count($tagCollection)) :
            $listTags = [];
            foreach ($tagCollection as $tag) {
                $listTags[] = '<a class="mp-info" href="' . $this->getTagUrl($tag) . '">' . $tag->getName() . '</a>';
            }
            $result = implode(', ', $listTags);
        endif;

        return $result;
    }
}
