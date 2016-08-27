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

class Orderby implements \Magento\Framework\Option\ArrayInterface
{
    const SOCIAL = 'social';
    const REVERSE_TIME  = 'reverse_time';
    const TIME  = 'time';

    public function toOptionArray()
    {
        return [['value' => self::SOCIAL, 'label' => __('Social')], ['value' => self::REVERSE_TIME, 'label' => __('Reverse time')], ['value' => self::TIME, 'label' => __('Time')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [self::SOCIAL => __('Social'), self::REVERSE_TIME => __('Reverse time'), self::TIME => __('Time')];
    }

    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
