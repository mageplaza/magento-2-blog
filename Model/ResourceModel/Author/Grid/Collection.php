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

namespace Mageplaza\Blog\Model\ResourceModel\Author\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Class Collection
 * @package Mageplaza\Blog\Model\ResourceModel\Author\Grid
 */
class Collection extends SearchResult
{
    /**
     * @return $this|SearchResult|void
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->joinLeft(
            ['cus' => $this->getTable('customer_entity')],
            'main_table.customer_id = cus.entity_id',
            ['email']
        )->joinLeft(
            ['post' => $this->getTable('mageplaza_blog_post')],
            'main_table.user_id = post.author_id',
            ['qty_post' => 'COUNT(post_id)']
        )->group('main_table.user_id');

        $this->addFilterToMap('name', 'main_table.name');
        $this->addFilterToMap('url_key', 'main_table.url_key');

        return $this;
    }

    /**
     * @param array|string $field
     * @param null $condition
     *
     * @return SearchResult
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($field === 'qty_post') {
            foreach ($condition as $key => $value) {
                if ($key === 'like') {
                    $this->getSelect()->having('COUNT(post_id) LIKE ?', $value);
                }
            }
            return $this;
        }

        return parent::addFieldToFilter($field, $condition);
    }
}
