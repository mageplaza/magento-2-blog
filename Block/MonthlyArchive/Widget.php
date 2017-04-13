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

class Widget extends Frontend
{
	public function getPostList()
	{
		return $this->helperData->getPostList();
	}

	public function getPostDate()
	{
		$posts = $this->helperData->getPostList();
		$postDates = array();
		if($posts) {
			foreach ($posts as $post) {
				$postDates[] = $post->getCreatedAt();
			}
		}
		return $postDates;
	}

	public function getDateArray(){
		$dateArray = array();
		foreach ($this->getPostDate() as $postDate){
			$dateArray[] = date("F Y",$this->dateTime->timestamp($postDate));
		}

		return $dateArray;
	}
	public function getDateArrayCount()
	{
		return $dateArrayCount = array_values(array_count_values($this->getDateArray()));
	}

	public function getDateArrayUnique()
	{
		return $dateArrayUnique=array_values(array_unique($this->getDateArray()));
	}
	public function getDateCount()
	{
		$count=0;
		$dateArrayCount = $this->getDateArrayCount();
		foreach ($dateArrayCount as $dateCount){
			$count++;
		}
		return $count;
	}

	public function getCurrentUrl($query)
	{

		return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true, '_query' => $query]);
	}

}
