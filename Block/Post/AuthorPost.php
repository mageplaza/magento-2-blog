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

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\Post;

/**
 * Class AuthorPost
 * @package Mageplaza\Blog\Block\Post
 */
class AuthorPost extends \Mageplaza\Blog\Block\Listpost
{

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getPostCollection()
    {
        $collection = parent::getPostCollection();

        $userId = $this->getAuthor()->getId();

        $collection->addFieldToFilter('author_id', $userId);

        return $collection;
    }

    /**
     * @param $postCollection
     *
     * @return string
     */
    public function getPostDatas($postCollection)
    {
        $result = [];

        /** @var Post $post */
        foreach ($postCollection->getItems() as $post) {
            $post->getCategoryIds();
            $post->getTopicIds();
            $post->getTagIds();
            $result[$post->getId()] = $post->getData();
        }

        return Data::jsonEncode($result);
    }

    public function getAuthorName()
    {
        return $this->getAuthor()->getName();
    }

    public function getAuthor()
    {
        return $this->coreRegistry->registry('mp_author');
    }

    public function getBlogTitle($meta = false)
    {
        return $meta ? [$this->getAuthor()->getName()] : $this->getAuthor()->getName();
    }

    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }
}
