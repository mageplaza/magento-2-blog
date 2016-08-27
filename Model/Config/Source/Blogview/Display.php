<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace Mageplaza\Blog\Model\Config\Source\Blogview;

class Display implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */

    const LIST_VIEW = 1;
    const GRID = 2;

    public function toOptionArray()
    {
        return [ ['value' => self::LIST_VIEW, 'label' => __('List View')], ['value' => self::GRID, 'label' => __('Grid View')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [self::LIST_VIEW => __('List View'), self::GRID => __('Grid View')];
    }
}
