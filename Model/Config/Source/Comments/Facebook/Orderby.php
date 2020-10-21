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

namespace Mageplaza\Blog\Model\Config\Source\Comments\Facebook;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Orderby
 * @package Mageplaza\Blog\Model\Config\Source\Comments\Facebook
 */
class Orderby implements ArrayInterface
{
    const SOCIAL = 'social';
    const REVERSE_TIME = 'reverse_time';
    const TIME = 'time';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
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

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return $this->toOptionArray();
    }
}
