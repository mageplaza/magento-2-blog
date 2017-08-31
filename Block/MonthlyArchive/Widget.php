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
namespace Mageplaza\Blog\Block\MonthlyArchive;

use Mageplaza\Blog\Block\Frontend;

/**
 * Class Widget
 * @package Mageplaza\Blog\Block\MonthlyArchive
 */
class Widget extends Frontend
{

	/**
	 * @return array
	 */
    public function getDateArrayCount()
    {
        return $this->helperData->getDateArrayCount();
    }

	/**
	 * @return array
	 */
    public function getDateArrayUnique()
    {
        return $this->helperData->getDateArrayUnique();
    }

	/**
	 * @return int|mixed
	 */
    public function getDateCount()
    {
        return $this->helperData->getDateCount();
    }

	/**
	 * @param $month
	 * @return string
	 */
    public function getMonthlyUrl($month)
    {
        return $this->helperData->getMonthlyUrl($month);
    }

	/**
	 * @return array
	 */
    public function getDateLabel()
    {
        return $this->helperData->getDateLabel();
    }
}
