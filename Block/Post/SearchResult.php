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

use Mageplaza\Blog\Model\ResourceModel\Post\Collection;

/**
 * Post list filtered by the blog search term.
 *
 * @package Mageplaza\Blog\Block\Post
 */
class SearchResult extends \Mageplaza\Blog\Block\ListPost
{
    /**
     * Override this function to apply collection for each type
     *
     * @return Collection|null
     */
    protected function getCollection()
    {
        $query = $this->getQuery();
        if ($query === '') {
            return null;
        }

        $collection = $this->helperData->getPostList();
        $collection->addFieldToFilter(
            ['name', 'short_description'],
            [['like' => '%' . $query . '%'], ['like' => '%' . $query . '%']]
        );

        return $collection;
    }

    /**
     * Search term from the request.
     *
     * @return string
     */
    public function getQuery()
    {
        return trim((string) $this->getRequest()->getParam('query', ''));
    }

    /**
     * @param bool $meta
     *
     * @return array|string|\Magento\Framework\Phrase
     */
    public function getBlogTitle($meta = false)
    {
        $query = $this->getQuery();
        if ($query === '') {
            return parent::getBlogTitle($meta);
        }

        $title = __('Search results for "%1"', $query);

        return $meta ? [$title] : $title;
    }
}
