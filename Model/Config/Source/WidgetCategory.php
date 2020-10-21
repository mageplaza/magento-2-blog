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

namespace Mageplaza\Blog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Class WidgetCategory
 * @package Mageplaza\Blog\Model\Config\Source\Widget
 */
class WidgetCategory implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    private $category;

    /**
     * WidgetCategory constructor.
     *
     * @param CollectionFactory $category
     */
    public function __construct(
        CollectionFactory $category
    ) {
        $this->category = $category;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->category->create();
        $ar = [];
        foreach ($collection->getItems() as $item) {
            if ($item->getId() === '1') {
                continue;
            }
            $ar[] = ['value' => $item->getId(), 'label' => $item->getName()];
        }

        return $ar;
    }
}
