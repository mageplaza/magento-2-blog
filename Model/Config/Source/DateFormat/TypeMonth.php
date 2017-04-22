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
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Blog\Model\Config\Source\DateFormat;

class TypeMonth implements \Magento\Framework\Option\ArrayInterface
{
	/**
	 * Options getter
	 *
	 * @return array
	 */
	const DATE = 3;
	const LONG_DATE = 2;
	const DEFAULT_DATE = 1;

	public function toOptionArray()
	{
		return [
			['value' => self::DEFAULT_DATE, 'label' => __('mm - yyyy')],
			['value' => self::LONG_DATE, 'label' => __('yyyy - mm')],
			['value' => self::DATE, 'label' => __('month yyyy')]
		];
	}

	/**
	 * Get options in "key-value" format
	 *
	 * @return array
	 */
	public function toArray()
	{
		return [
			self::LONG_DATE => __('yyyy - mm'),
			self::DEFAULT_DATE => __('mm - yyyy'),
			self::DATE => __('month yyyy')
		];
	}
}
