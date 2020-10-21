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
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Ui\Component\Blog\Form\Categories;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;
use Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

/**
 * Options tree for "Categories" field
 */
class Options implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var array
     */
    protected $categoriesTree;

    /**
     * Options constructor.
     *
     * @param CategoryCollectionFactory $categoryCollectionFactory
     */
    public function __construct(CategoryCollectionFactory $categoryCollectionFactory)
    {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $this->getCategoriesTree();
    }

    /**
     * @return mixed
     */
    public function getCategoriesTree()
    {
        if ($this->categoriesTree === null) {
            /* @var $collection Collection */
            $collection = $this->categoryCollectionFactory->create();

            $categoryById = [
                Category::TREE_ROOT_ID => [
                    'value' => Category::TREE_ROOT_ID,
                    'optgroup' => []
                ],
            ];

            foreach ($collection as $category) {
                foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                    if (!isset($categoryById[$categoryId])) {
                        $categoryById[$categoryId] = ['value' => $categoryId];
                    }
                }

                $categoryById[$category->getId()]['is_active'] = 1;
                $categoryById[$category->getId()]['label'] = $category->getName();
                $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
            }

            $this->categoriesTree = $categoryById[Category::TREE_ROOT_ID]['optgroup'];
        }

        return $this->categoriesTree;
    }
}
