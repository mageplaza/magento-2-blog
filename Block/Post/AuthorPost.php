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

/**
 * Class AuthorPost
 * @package Mageplaza\Blog\Block\Post
 */
class AuthorPost extends \Mageplaza\Blog\Block\Listpost
{
    public function getPostCollection()
    {
        $collection = parent::getPostCollection();

        $userId = $this->getAuthor()->getId();

        $collection->addFieldToFilter('user_id', $userId);
        return $collection;
    }

    public function getAuthorName()
    {
        return $this->getAuthor()->getName();
    }

    public function getAuthor()
    {
        return $this->coreRegistry->registry('mp_author');
    }
}
