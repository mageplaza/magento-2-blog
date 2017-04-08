<?php
/**
 * Created by PhpStorm.
 * User: Admin
 * Date: 3/31/2017
 * Time: 5:24 PM
 */
namespace Mageplaza\Blog\Block\Widget;

use Magento\Widget\Block\BlockInterface;
use Mageplaza\Blog\Block\Frontend;
class Posts extends Frontend implements BlockInterface{

	protected $_template = "widget/posts.phtml";

	public function getCollection(){
		if ($this->hasData('show_type') && $this->getData('show_type')=='category')
		{
			$postsCollection=$this->helperData->categoryfactory->create()->load($this->getData('category_id'))->getSelectedPostsCollection();
		}
		else {
			$postsCollection = $this->helperData->postfactory->create()->getCollection();
		}
		$postsCollection
			->addOrder('created_at')
			->setPageSize($this->getData('post_count'))
		;
		return $postsCollection;

	}


}