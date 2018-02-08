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
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Model\ResourceModel\Comment\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

/**
 * Class Collection
 * @package Mageplaza\Blog\Model\ResourceModel\Comment\Grid
 */
class Collection extends SearchResult
{
    /**
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $this->addPostName();
        $this->addCustomerName();
        return $this;
    }

    /**
     * @return $this
     */
    public function addPostName()
    {
        $this->getSelect()->join(
            ['mp' => $this->getTable('mageplaza_blog_post')],
            "main_table.post_id = mp.post_id",
            ['post_name' => 'name']
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function addCustomerName()
    {
        $this->getSelect()->join(
            ['ce' => $this->getTable('customer_entity')],
            "main_table.entity_id = ce.entity_id",
            ["customer_name" => "CONCAT(`ce`.`firstname`,' ',`ce`.`lastname`)"]
        );

        return $this;
    }
}
