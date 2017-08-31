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
namespace Mageplaza\Blog\Block\Post;

use Mageplaza\Blog\Block\Frontend;

/**
 * Class Relatedpost
 * @package Mageplaza\Blog\Block\Post
 */
class Relatedpost extends Frontend
{

    public function _construct()
    {

        $this->setTabTitle();
    }

	/**
	 * @return mixed
	 */
    public function getCurrentProduct()
    {
        return $this->getRequest()->getParam('id');
    }

	/**
	 * @param $id
	 * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
	 */
	public function getRelatedPostList($id)
	{
		return $this->helperData->getRelatedPostList($id);
	}

	/**
	 * @return int|mixed
	 */
	public function getLimitPosts()
	{
		$limitRelated = ($this->getBlogConfig('product_post/product_detail/post_limit')==''
            || $this->getBlogConfig('product_post/product_detail/post_limit')==0)
            ? 1
            : $this->getBlogConfig('product_post/product_detail/post_limit');
		return $limitRelated;
	}


	public function setTabTitle()
	{
		$posts = $this->getRelatedPostList($this->getCurrentProduct());
		$countPost = count($posts);
		$title = ($this->getLimitPosts()>$countPost) ?  __('Related Posts ('.$countPost.')') : __('Related Posts ('.$this->getLimitPosts().')');
		$this->setTitle($title);
	}

}
