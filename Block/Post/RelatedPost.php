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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Post;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Image;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;

/**
 * Class RelatedPost
 * @package Mageplaza\Blog\Block\Post
 */
class RelatedPost extends Template
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var Collection
     */
    protected $_relatedPosts;

    /**
     * @var int
     */
    protected $_limitPost;

    /**
     * RelatedPost constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param Data $helperData
     * @param array $data
     *
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $helperData,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->helperData = $helperData;

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
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getRelatedPostList()
    {
        if ($this->_relatedPosts == null) {
            /** @var Collection $collection */
            $collection = $this->helperData->getPostList();
            $collection->getSelect()
                ->join(
                    [
                        'related' => $collection->getTable('mageplaza_blog_post_product')
                    ],
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
        if (!$this->_limitPost) {
            $this->_limitPost = (int)$this->helperData->getBlogConfig('product_post/product_detail/post_limit') ?: 1;
        }

        return $this->_limitPost;
    }

    /**
     * Set tab title
     *
     * @return void
     * @throws NoSuchEntityException
     */
    public function setTabTitle()
    {
        $relatedSize = min($this->getRelatedPostList()->getSize(), $this->getLimitPosts());
        $title = $relatedSize
            ? __('Related Posts %1', '<span class="counter">' . $relatedSize . '</span>')
            : __('Related Posts');
        if ($this->helperData->isEnabled()) {
            $this->setTitle($title);
        }
    }

    /**
     * @return bool
     */
    public function isEnabledBlog()
    {
        return $this->helperData->isEnabled();
    }

    /**
     * @return bool
     */
    public function getRelatedMode()
    {
        return (int)$this->helperData->getConfigGeneral('related_mode') === 1;
    }

    /**
     * Resize Image Function
     *
     * @param $image
     * @param null $size
     * @param string $type
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function resizeImage($image, $size = null, $type = Image::TEMPLATE_MEDIA_TYPE_POST)
    {
        if (!$image) {
            return $this->getDefaultImageUrl();
        }

        return $this->helperData->getImageHelper()->resizeImage($image, $size, $type);
    }

    /**
     * get default image url
     */
    public function getDefaultImageUrl()
    {
        return $this->getViewFileUrl('Mageplaza_Blog::media/images/mageplaza-logo-default.png');
    }
}
