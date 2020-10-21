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

namespace Mageplaza\Blog\Model\Config\Source\Comments;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 * @package Mageplaza\Blog\Model\Config\Source\Comments
 */
class Type implements ArrayInterface
{
    const DEFAULT_COMMENT = 1;
    const FACEBOOK = 2;
    const DISQUS = 3;
    const DISABLE = 4;

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
        return [
            self::DEFAULT_COMMENT => __('Default Comment'),
            self::DISQUS => __('Disqus Comment'),
            self::FACEBOOK => __('Facebook Comment'),
            self::DISABLE => __('Disable Completely')
        ];
    }
}
