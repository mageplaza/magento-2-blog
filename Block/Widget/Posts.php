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
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Widget;

use Magento\Widget\Block\BlockInterface;
use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Posts
 * @package Mageplaza\Blog\Block\Widget
 */
class Posts extends Frontend implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = "widget/posts.phtml";

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|\Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getCollection()
    {
        if ($this->hasData('show_type') && $this->getData('show_type') === 'category') {
            $collection = $this->helperData->getObjectByParam($this->getData('category_id'), null, Data::TYPE_CATEGORY)
                ->getSelectedPostsCollection();
            $this->helperData->addStoreFilter($collection);
        } else {
            $collection = $this->helperData->getPostList();
        }

        $collection->setPageSize($this->getData('post_count'));

        return $collection;
    }

    /**
     * @return \Mageplaza\Blog\Helper\Data
     */
    public function getHelperData()
    {
        return $this->helperData;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * @param $code
     * @return string
     */
    public function getBlogUrl($code)
    {
        return $this->helperData->getBlogUrl($code);
    }
}
