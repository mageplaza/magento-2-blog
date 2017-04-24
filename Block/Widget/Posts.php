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
namespace Mageplaza\Blog\Block\Widget;

use Magento\Widget\Block\BlockInterface;
use Mageplaza\Blog\Block\Frontend;

class Posts extends Frontend implements BlockInterface{

	protected $_template = "widget/posts.phtml";

	public function getCollection(){
		if ($this->hasData('show_type') && $this->getData('show_type')==='category')
		{
			$postsCollection=$this->helperData->categoryfactory->create()->load($this->getData('category_id'))->getSelectedPostsCollection();
		}
		else {
			$postsCollection = $this->helperData->postfactory->create()->getCollection();
		}
		$postsCollection->addOrder('created_at')->setPageSize($this->getData('post_count'));
		return $postsCollection;
	}
	public function getTitle(){

		return $this->getData('title');
	}

	public function getBlogUrl($code){

		return $this->helperData->getBlogUrl($code);
	}
}