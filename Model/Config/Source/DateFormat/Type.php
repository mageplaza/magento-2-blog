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

namespace Mageplaza\Blog\Model\Config\Source\DateFormat;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Type
 * @package Mageplaza\Blog\Model\Config\Source\DateFormat
 */
class Type implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */

    public function toOptionArray()
    {
        $dateArray = [];
        $type = [
            'F j, Y',
            'Y-m-d',
            'm/d/Y',
            'd/m/Y',
            'F j, Y g:i a',
            'F j, Y g:i A',
            'Y-m-d g:i a',
            'Y-m-d g:i A',
            'd/m/Y g:i a',
            'd/m/Y g:i A',
            'm/d/Y H:i',
            'd/m/Y H:i',
        ];
        foreach ($type as $item) {
            $dateArray[] = [
                'value' => $item,
                'label' => $item . ' (' . date($item) . ')'
            ];
        }

        return $dateArray;
    }
}
