<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
 */
namespace Mageplaza\Blog\Model\Config\Source\Comments\Facebook;

class Colorscheme implements \Magento\Framework\Option\ArrayInterface
{
    const LIGHT = 'light';
    const DARK  = 'dark';

    public function toOptionArray()
    {
        return [['value' => self::LIGHT, 'label' => __('Light')], ['value' => self::DARK, 'label' => __('Dark')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [self::LIGHT => __('Light'), self::DARK => __('Dark')];
    }

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
