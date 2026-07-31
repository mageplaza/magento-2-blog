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

namespace Mageplaza\Blog\Block\Sidebar;

use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Search
 * @package Mageplaza\Blog\Block\Sidebar
 */
class Search extends Frontend
{
    /**
     * AJAX endpoint URL for the blog search autocomplete.
     *
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->helperData->getUrl('mpblog/post/search');
    }

    /**
     * @return string
     */
    public function getSearchJsonConfig()
    {
        return Data::jsonEncode([
            'serviceUrl'   => $this->getSearchUrl(),
            'minChars'     => (int) $this->getSidebarConfig('search/min_chars') ?: 1,
            'visibleImage' => (int) $this->getSidebarConfig('search/show_image'),
        ]);
    }

    /**
     * Suggestions for a search term. Filters in SQL and caps the result so it
     * never loads the whole post table (the old getSearchBlogData dumped every
     * post into the page -> OOM at scale).
     *
     * @param string $query
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function getSearchSuggestions($query)
    {
        $query = trim((string) $query);
        if ($query === '') {
            return [];
        }

        $collection = $this->helperData->getPostList();
        $collection->addFieldToFilter(
            ['name', 'short_description'],
            [['like' => '%' . $query . '%'], ['like' => '%' . $query . '%']]
        )->setPageSize($this->getSearchLimit());

        return $this->buildSuggestions($collection);
    }

    /**
     * Kept for backward compatibility, but capped so it can no longer load the
     * entire post table; templates now use the AJAX endpoint instead.
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getSearchBlogData()
    {
        $collection = $this->helperData->getPostList()->setPageSize($this->getSearchLimit());

        return Data::jsonEncode($this->buildSuggestions($collection));
    }

    /**
     * @param \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection
     *
     * @return array
     */
    protected function buildSuggestions($collection)
    {
        $result    = [];
        $limitDesc = (int) $this->getSidebarConfig('search/description');
        foreach ($collection as $item) {
            $shortDescription = ($item->getShortDescription() && $limitDesc > 0) ?
                $item->getShortDescription() : '';
            if (strlen($shortDescription) > $limitDesc) {
                $shortDescription = mb_substr($shortDescription, 0, $limitDesc, 'UTF-8') . '...';
            }

            $result[] = [
                'value' => $item->getName(),
                'url'   => $item->getUrl(),
                'image' => $this->resizeImage($item->getImage(), '100x'),
                'desc'  => $shortDescription
            ];
        }

        return $result;
    }

    /**
     * @return int
     */
    protected function getSearchLimit()
    {
        return (int) $this->getSidebarConfig('search/search_limit') ?: 10;
    }

    /**
     * get sidebar config
     *
     * @param $code
     * @param $storeId
     *
     * @return mixed
     */
    public function getSidebarConfig($code, $storeId = null)
    {
        return $this->helperData->getBlogConfig('sidebar/' . $code, $storeId);
    }
}
