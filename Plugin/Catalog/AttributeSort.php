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
use Magento\Framework\Registry;
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
    private $request;

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * Topmenu constructor.
     *
     * @param RequestInterface $request
     * @param Registry $coreRegistry
     * @param Data $helper
     */
    public function __construct(
        RequestInterface $request,
        Registry $coreRegistry,
        Data $helper
    ) {
        $this->helper       = $helper;
        $this->request      = $request;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param Collection $productCollection
     * @param callable $proceed
     * @param $attribute
     * @param $dir
     *
     * @return Collection
     */
    public function aroundAddAttributeToSort(
        Collection $productCollection,
        callable $proceed,
        $attribute,
        $dir
    ) {
        $post_id = $this->getPostId();
        if ($post_id && $attribute === 'position') {
            $productCollection->getSelect()->where('mp_p.post_id = ' . $post_id);
        } else {
            $productCollection->getSelect()->group('e.entity_id');
        }

        if ($attribute === 'position' &&
            in_array(
                $this->request->getFullActionName(),
                ['mageplaza_blog_post_products', 'mageplaza_blog_post_productsGrid'],
                true
            )) {
            $productCollection->getSelect()->order('position ' . $dir);

            return $productCollection;
        }

        return $proceed($attribute, $dir);
    }

    /**
     * @param Collection $productCollection
     * @param callable $proceed
     * @param $attribute
     * @param $condition
     * @param $joinType
     *
     * @return Collection
     */
    public function aroundAddAttributeToFilter(
        Collection $productCollection,
        callable $proceed,
        $attribute,
        $condition,
        $joinType
    ) {
        $post_id = $this->getPostId();
        if ($post_id && $attribute === 'position') {
            $productCollection->getSelect()->where('mp_p.post_id = ' . $post_id);
        } else {
            $productCollection->getSelect()->group('e.entity_id');
        }

        if ($attribute === 'position' &&
            in_array(
                $this->request->getFullActionName(),
                ['mageplaza_blog_post_products', 'mageplaza_blog_post_productsGrid'],
                true
            )) {
            $from = isset($condition['from']) ? $attribute . ' >= ' . $condition['from'] : '';
            $to   = isset($condition['to']) ? $attribute . ' <= ' . $condition['to'] : '';

            if ($from && $to) {
                if ($condition['to'] === $condition['from']) {
                    $conditionSql = $attribute . ' = ' . $condition['to'];
                } else {
                    $conditionSql = $to . ' && ' . $from;
                }
            } elseif ($from) {
                $conditionSql = $from;
            } elseif ($to) {
                $conditionSql = $to;
            } else {
                $conditionSql = '';
            }

            if ($conditionSql) {
                $productCollection->getSelect()->where($conditionSql);

                return $productCollection;
            }
        }

        return $proceed($attribute, $condition, $joinType);
    }

    /**
     * @return string
     */
    public function getPostId()
    {
        if ($this->request->getParam('filter') === '') {
            return null;
        }
        $post = $this->coreRegistry->registry('mageplaza_blog_post');
        return $post->getId()?:$this->request->getParam('post_id');
    }
}
