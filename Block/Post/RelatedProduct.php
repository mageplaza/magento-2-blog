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

use Exception;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Helper\Output;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
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
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var Output
     */
    protected $outputHelper;

    /**
     * @var Configurable
     */
    protected $catalogProductTypeConfigurable;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * RelatedProduct constructor.
     *
     * @param Context $context
     * @param PostHelper $postDataHelper
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param CollectionFactory $productCollectionFactory
     * @param Configurable $catalogProductTypeConfigurable
     * @param ProductRepository $productRepository
     * @param HelperData $helperData
     * @param Output $output
     * @param Data $urlHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        PostHelper $postDataHelper,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        CollectionFactory $productCollectionFactory,
        Configurable $catalogProductTypeConfigurable,
        ProductRepository $productRepository,
        HelperData $helperData,
        Output $output,
        Data $urlHelper,
        array $data = []
    ) {
        $this->_productCollectionFactory      = $productCollectionFactory;
        $this->helper                         = $helperData;
        $this->outputHelper                   = $output;
        $this->catalogProductTypeConfigurable = $catalogProductTypeConfigurable;
        $this->productRepository              = $productRepository;

        parent::__construct($context, $postDataHelper, $layerResolver, $categoryRepository, $urlHelper, $data);
    }

    /**
     * @return Output
     */
    public function getHelper()
    {
        return $this->outputHelper;
    }

    /**
     * @return mixed
     */
    public function relatedMode()
    {
        return $this->helper->getModuleConfig('product_post/post_detail/related_mode');
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
     * Get ProductCollection in same brand (filter by Attribute Option_Id)
     *
     * @return mixed
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
                    'e.entity_id = product_post.entity_id'
                )
                ->where('product_post.post_id = ' . $postId)
                ->order('product_post.position ASC')
                ->limit((int) $this->helper->getBlogConfig('product_post/post_detail/product_limit') ?: self::LIMIT);

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

    /**
     * @param Product $product
     *
     * @return mixed
     */
    public function getParentProductUrl($product)
    {
        $parentByChild = $this->catalogProductTypeConfigurable->getParentIdsByChild($product->getId());
        if (isset($parentByChild[0])) {
            try {
                $parentProduct = $this->productRepository->getById($parentByChild[0]);

                return $parentProduct->getProductUrl();
            } catch (Exception $e) {
                return $product->getProductUrl();
            }
        }

        return $product->getProductUrl();
    }
}
