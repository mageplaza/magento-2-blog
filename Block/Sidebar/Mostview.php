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
namespace Mageplaza\Blog\Block\Sidebar;

use Mageplaza\Blog\Block\Frontend;

class Mostview extends Frontend
{
	public function getFormatCreatedAt($date)
	{
		$dateType = $this->helperData->getBlogConfig('general/date_type');
		switch ($dateType) {
			case 1:
				$dateFormat    = $this->dateTime->formatDate($date, false);
				break;
			case 2:
				$dateFormat = date_format(date_create($date),"Y M d");
				break;
			case 3:
				$dateFormat = date_format(date_create($date),"d/m/Y");
				break;
			case 4:
				$dateFormat = date_format(date_create($date),"Y/m/d h:m:s");
				break;
		}
//		$dateFormat = date_format(date_create($this->getCreatedAt()),"Y/m/d h:m:s");
//		$dateFormat    = $this->dateTime->formatDate($this->getCreatedAt(), false);
		return $dateFormat;
	}
    public function getMosviewPosts()
    {
        return $this->helperData->getMosviewPosts();
    }

    public function getRecentPost()
    {
        return $this->helperData->getRecentPost();
    }
}
