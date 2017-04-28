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

class Type implements \Magento\Framework\Option\ArrayInterface
{
	/**
	 * Options getter
	 *
	 * @return array
	 */
	const DATE = 3;
	const LONG_DATE = 2;
	const DEFAULT_DATE = 1;
	const FULL_DATE = 4;

	public function toOptionArray()
	{
		return [
			['value' => self::DEFAULT_DATE, 'label' => __('yyyy - mm - dd')],
			['value' => self::LONG_DATE, 'label' => __('yyyy month dd')],
			['value' => self::DATE, 'label' => __('dd/mm/yyyy')],
			['value' => self::FULL_DATE, 'label' => __('yyyy/mm/dd hh:mm:ss')]
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
			self::FULL_DATE => __('yyyy/mm/dd hh:mm:ss'),
			self::DEFAULT_DATE => __('yyyy - mm - dd'),
			self::DATE => __('dd/mm/yyyy'),
			self::LONG_DATE=> __('yyyy month dd')
		];
	}
}
