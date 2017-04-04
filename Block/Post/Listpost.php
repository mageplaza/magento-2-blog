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

class Listpost extends Frontend
{

//    public function getPostList()
//    {
//        $collection = $this->helperData->getPostList();
//
//        if ($collection && $collection->getSize()) {
//            // create pager block for collection
//            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'mp.blog.post.pager');
//            // assign collection to pager
//            $pager->setLimit($this->helperData->getBlogConfig('general/pagination'))->setCollection($collection);
//            $this->setChild('pager', $pager);// set pager block in layout
//        }

//        return $collection;
//		return $this->getBlogPagination();
//    }

    public function checkRss()
    {
        return $this->helperData->getBlogUrl('post/rss');
    }
    public function getMonthParam()
	{
		return $this->getRequest()->getParam('month');
	}
}
