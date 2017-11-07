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

use Magento\Framework\Registry;
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
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    protected $_relatedPosts;

    /**
     * @var int
     */
    protected $_limitPost;

    /**
     * Relatedpost constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Mageplaza\Blog\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helperData,
        array $data = []
    )
    {
        $this->_coreRegistry = $registry;
        $this->helperData    = $helperData;

        parent::__construct($context, $data);

        $this->setTabTitle();
    }

    /**
     * Get current product id
     *
     * @return null|int
     */
    public function getProductId()
    {
        $product = $this->_coreRegistry->registry('product');

        return $product ? $product->getId() : null;
    }

    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getRelatedPostList()
    {
        if ($this->_relatedPosts == null) {
            /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
            $collection = $this->helperData->getPostList();
            $collection->getSelect()
                ->join([
                    'related' => $collection->getTable('mageplaza_blog_post_product')],
                    'related.post_id=main_table.post_id AND related.entity_id=' . $this->getProductId()
                )
                ->limit($this->getLimitPosts());

            $this->_relatedPosts = $collection;
        }

        return $this->_relatedPosts;
    }

    /**
     * @return int|mixed
     */
    public function getLimitPosts()
    {
        if ($this->_limitPost == null) {
            $this->_limitPost = (int)$this->helperData->getBlogConfig('product_post/product_detail/post_limit') ?: 1;
        }

        return $this->_limitPost;
    }

    /**
     * Set tab title
     *
     * @return void
     */
    public function setTabTitle()
    {
        $relatedSize = min($this->getRelatedPostList()->getSize(), $this->getLimitPosts());
        $title       = $relatedSize
            ? __('Related Posts %1', '<span class="counter">' . $relatedSize . '</span>')
            : __('Related Posts');

        $this->setTitle($title);
    }
}
