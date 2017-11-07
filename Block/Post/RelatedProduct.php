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

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Url\Helper\Data;
use Mageplaza\Blog\Helper\Data as HelperData;

/**
 * Class RelatedProduct
 * @package Mageplaza\Blog\Block\Post
 */
class RelatedProduct extends ListProduct
{
    /**
     * Default related product page title
     */
    const TITLE = 'Related Products';

    /**
     * Default limit related products
     */
    const LIMIT = '12';

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    protected $helper;

    /**
     * RelatedProduct constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Mageplaza\Blog\Helper\Data $helperData
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $productCollectionFactory,
        HelperData $helperData,
        Data $urlHelper,
        array $data = []
    )
    {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->helper                    = $helperData;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    /**
     * @return mixed
     */
    public function hasProduct()
    {
        $collection = $this->_getProductCollection();

        return $collection->getSize();
    }

    /**
     * @return mixed
     * get ProductCollection in same brand ( filter by Atrribute Option_Id )
     */
    public function _getProductCollection()
    {
        if ($this->_productCollection === null) {
            $postId     = $this->getRequest()->getParam('id');
            $collection = $this->_productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addStoreFilter();

            $collection->getSelect()
                ->join(
                    ['product_post' => $collection->getTable('mageplaza_blog_post_product')],
                    "e.entity_id = product_post.entity_id"
                )
                ->where('product_post.post_id = ' . $postId)
                ->order('product_post.position ASC')
                ->limit((int)$this->helper->getBlogConfig('product_post/post_detail/product_limit') ?: self::LIMIT);

            $this->_productCollection = $collection;
        }

        return $this->_productCollection;
    }

    /**
     * @inheritdoc
     */
    public function getMode()
    {
        return 'grid';
    }

    /**
     * @inheritdoc
     */
    public function getToolbarHtml()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getAdditionalHtml()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    protected function _beforeToHtml()
    {
        return $this;
    }
}
