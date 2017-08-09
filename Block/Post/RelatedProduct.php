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
use Mageplaza\Blog\Helper\Data as HelperData;

class Relatedproduct extends ListProduct
{
    protected $_productCollectionFactory;
    protected $visibleProduts;
    protected $limit;
    protected $helper;
    /*
    * Default related product page title
    */
    const TITLE = 'Related Products';

    /*
    * Default limit related products
    */
    const LIMIT = '12';

    public function __construct(Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $visibleProduts,
        HelperData $helperData,
        \Magento\Framework\Url\Helper\Data $urlHelper, array $data = []
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->visibleProduts = $visibleProduts;
        $this->helper = $helperData;
        $this->limit = $this->helper->getBlogConfig('product_post/post_detail/product_limit');
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
            $data
        );
    }

    /**
     * @return mixed
     * get ProductCollection in same brand ( filter by Atrribute Option_Id )
     */

    public function _getProductCollection()
    {
        $limit = ($this->limit) ? $this->limit : SELF::LIMIT;
        $postId = $this->getRequest()->getParam('id');
            $collection = $this->_productCollectionFactory->create();
            $collection->getSelect()->joinLeft(['product_post' => $collection->getTable('mageplaza_blog_post_product')]
                ,"e.entity_id = product_post.entity_id")->where('product_post.post_id = '.$postId);
            $collection
                ->addAttributeToSelect('*');
            if ($limit > $collection->getSize()){
                return $collection;
            } else {
                return $collection->setPageSize($limit);
            }

    }

    public function getToolbarHtml()
    {
        return null;
    }

    public function getAdditionalHtml()
    {
        return null;
    }

}
