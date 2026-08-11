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

namespace Mageplaza\Blog\Block\Category;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Api\Data\CategoryInterface;
use Mageplaza\Blog\Helper\Data as HelperData;
use Mageplaza\Blog\Model\Category;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\ResourceModel\Category\Collection;
use Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory;

/**
 * Class Widget
 * @package Mageplaza\Blog\Block\Category
 */
class Menu extends Template
{
    /**
     * @var CollectionFactory
     */
    protected $categoryCollection;

    /**
     * @var CategoryFactory
     */
    protected $category;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Menu constructor.
     *
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param CategoryFactory $categoryFactory
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        CategoryFactory $categoryFactory,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->categoryCollection = $collectionFactory;
        $this->category           = $categoryFactory;
        $this->helper             = $helperData;
        $this->storeManager       = $storeManager;

        parent::__construct($context, $data);
    }

    /**
     * @param $id
     *
     * @return CategoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getChildCategory($id)
    {
        $collection = $this->categoryCollection->create()->addAttributeToFilter('parent_id', $id)
            ->addAttributeToFilter('enabled', '1');
        $this->helper->addStoreFilter($collection, $this->storeManager->getStore()->getId());

        return $collection->getItems();
    }

    /**
     * Drop categories already expanded on the current branch. A cycle in the
     * parent_id chain (e.g. a row whose parent_id is its own id) would otherwise
     * recurse until the request exhausts memory.
     *
     * @param CategoryInterface[] $categories
     * @param array $visited
     *
     * @return CategoryInterface[]
     */
    private function filterVisited($categories, array $visited)
    {
        return array_filter($categories, static function ($category) use ($visited) {
            return !isset($visited[$category->getId()]);
        });
    }

    /**
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getCollections()
    {
        $collection = $this->categoryCollection->create()
            ->addAttributeToFilter('level', '1')->addAttributeToFilter('enabled', '1')->setOrder('position','ASC');

        return $this->helper->addStoreFilter($collection, $this->storeManager->getStore()->getId());
    }

    /**
     * @param Category $parentCategory
     *
     * @return string
     */
    public function getMenuHtml($parentCategory, array $visited = [])
    {
        $categoryUrl = $this->helper->getBlogUrl('category/' . $parentCategory->getUrlKey());
        $html = '<li class="level' . $parentCategory->getLevel()
            . ' category-item ui-menu-item" role="presentation">'
            . '<a href="' . $categoryUrl . '" class="ui-corner-all" tabindex="-1" role="menuitem">'
            . '<span>' . $parentCategory->getName() . '</span></a>';

        $visited[$parentCategory->getId()] = true;
        $childCategorys = $this->filterVisited($this->getChildCategory($parentCategory->getId()), $visited);

        if (count($childCategorys) > 0) {
            $html .= '<ul class="level' . $parentCategory->getLevel() . ' submenu ui-menu ui-widget'
                . ' ui-widget-content ui-corner-all"'
                . ' role="menu" aria-expanded="false" style="display: none; top: 47px; left: -0.15625px;"'
                . ' aria-hidden="true">';

            /** @var Category $childCategory */
            foreach ($childCategorys as $childCategory) {
                $html .= $this->getMenuHtml($childCategory, $visited);
            }
            $html .= '</ul>';
        }
        $html .= '</li>';

        return $html;
    }

    /**
     * @param Category $parentCategory
     *
     * @return string
     */
    public function getPortoMenuHtml($parentCategory, array $visited = [])
    {
        $categoryUrl = $this->helper->getBlogUrl('category/' . $parentCategory->getUrlKey());
        $html = '<li class="ui-menu-item level' . $parentCategory->getLevel() . ' parent" role="presentation">'
            . '<div class="open-children-toggle"></div>'
            . '<a href="' . $categoryUrl . '" class="ui-corner-all" tabindex="-1" role="menuitem">'
            . '<span>' . $parentCategory->getName() . '</span></a>';

        $visited[$parentCategory->getId()] = true;
        $childCategories = $this->filterVisited($this->getChildCategory($parentCategory->getId()), $visited);

        if (count($childCategories) > 0) {
            $html .= '<ul class="subchildmenu level' . $parentCategory->getLevel() . ''
                . ' ui-widget-content ui-corner-all"'
                . ' role="menu" aria-expanded="false"'
                . ' aria-hidden="true">';

            /** @var Category $childCategory */
            foreach ($childCategories as $childCategory) {
                $html .= $this->getMenuHtml($childCategory, $visited);
            }
            $html .= '</ul>';
        }
        $html .= '</li>';

        return $html;
    }

    /**
     * @return \Magento\Framework\Phrase|mixed
     * @throws NoSuchEntityException
     */
    public function getBlogHomePageTitle()
    {
        return $this->helper->getBlogConfig('display/name', $this->helper->getCurrentStoreId()) ?: __('Blog');
    }

    /**
     * @return string
     */
    public function getBlogHomeUrl()
    {
        return $this->helper->getBlogUrl('');
    }


    public function getBlogUrlByUrlKey($urlKey)
    {
        return $this->helper->getBlogUrl('category/' . $urlKey);
    }

    public function getChildDataCate($category, array $visited = [])
    {
        $visited[$category->getId()] = true;
        $childCategorys    = $this->filterVisited($this->getChildCategory($category->getId()), $visited);
        $childCategoryData = [];
        if (count($childCategorys) > 0) {
            foreach ($childCategorys as $childCategory) {
                $childCategoryUrl = $this->getBlogUrlByUrlKey($childCategory->getUrlKey());
                array_push($childCategoryData,
                    [
                        "name"             => $childCategory->getName(),
                        "id"               => "mg-blog" . $childCategory->getId(),
                        "url"              => $childCategoryUrl,
                        "image"            => false,
                        "has_active"       => false,
                        "is_active"        => false,
                        "is_category"      => true,
                        "is_parent_active" => true,
                        "position"         => null,
                        "path"             => "1/2/38",
                        "childData"        => $this->getChildDataCate($childCategory, $visited)
                    ]
                );
            }
            return $childCategoryData;
        } else {
            return [];
        }
    }
}
