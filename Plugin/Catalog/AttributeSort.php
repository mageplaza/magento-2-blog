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

namespace Mageplaza\Blog\Plugin\Catalog;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\App\RequestInterface;
use Mageplaza\Blog\Helper\Data;

/**
 * Class AttributeSort
 * @package Mageplaza\Blog\Plugin\Catalog
 */
class AttributeSort
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * AttributeSort constructor.
     *
     * @param RequestInterface $request
     * @param Data $helper
     */
    public function __construct(
        RequestInterface $request,
        Data $helper
    ) {
        $this->helper  = $helper;
        $this->request = $request;
    }

    public function aroundAddAttributeToSort(
        Collection $productCollection,
        callable $proceed,
        $attribute,
        $dir
    ) {
        if ($attribute === 'position' &&
            in_array(
                $this->request->getFullActionName(),
                ['mageplaza_blog_post_products', 'mageplaza_blog_post_productsGrid'],
                true
            )
        ) {
            $productCollection->getSelect()->order('position ' . $dir);

            return $productCollection;
        }

        return $proceed($attribute, $dir);
    }
}
