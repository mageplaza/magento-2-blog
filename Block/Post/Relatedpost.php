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

namespace Mageplaza\Blog\Block\Post;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Relatedpost
 * @package Mageplaza\Blog\Block\Post
 */
class Relatedpost extends Template
{
    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    protected $helperData;

    /**
     * Relatedpost constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageplaza\Blog\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        array $data = []
    )
    {
        $this->helperData = $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getRelatedPostList()
    {
        $currentPostId = $this->getRequest()->getParam('id');

        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->helperData->getPostList();
        $collection->getSelect()
            ->join([
                'related' => $collection->getTable('mageplaza_blog_post_product')],
                'related.post_id=main_table.post_id AND related.entity_id=' . $currentPostId . ' AND main_table.enabled=1'
            )
            ->limit($this->getLimitPosts());

        return $collection;
    }

    /**
     * @return int|mixed
     */
    public function getLimitPosts()
    {
        return (int)$this->helperData->getBlogConfig('product_post/product_detail/post_limit') ?: 1;
    }
}
