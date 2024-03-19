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

declare(strict_types=1);

namespace Mageplaza\Blog\Block\Category;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\View\Element\BlockInterface;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\Category;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;

/**
 * Class Listpost
 * @package Mageplaza\Blog\Block\Category
 */
class Listpost extends \Mageplaza\Blog\Block\Listpost
{
    protected ?Category $category = null;

    /**
     * Override this function to apply collection for each type
     *
     * @return Collection|null
     * @throws NoSuchEntityException
     */
    protected function getCollection()
    {
        if ($category = $this->getBlogObject()) {
            return $this->helperData->getPostCollection(Data::TYPE_CATEGORY, $category->getId());
        }

        return null;
    }

    protected function getBlogObject(): Category|null
    {
        if ($this->category instanceof Category === true) {
            return $this->category;
        }

        $id = $this->getRequest()->getParam('id');
        if (empty($id) === true) {
            return null;
        }

        $category = $this->helperData->getObjectByParam($id, null, Data::TYPE_CATEGORY);
        if (isset($category) === true
            && $category instanceof Category === true
            && $category->getId() !== null
        ) {
            $this->category = $category;
        }

        return $this->category ?? null;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function _prepareLayout(): void
    {
        parent::_prepareLayout();
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs instanceof BlockInterface === false && is_bool($breadcrumbs) === false) {
            return;
        }

        $category = $this->getBlogObject();
        if ($category instanceof Category === false) {
            return;
        }

        $categoryName = preg_replace('/[^A-Za-z0-9\-]/', ' ', $category->getName());

        $breadcrumbs->addCrumb(
            $category->getUrlKey(), [
            'label' => __($categoryName),
            'title' => __($categoryName),
        ]);
    }

    /**
     * @param bool $meta
     *
     * @return array|Phrase|string
     */
    public function getBlogTitle(
        $meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);
        $category = $this->getBlogObject();
        if (!$category) {
            return $blogTitle;
        }

        if ($meta) {
            if ($category->getMetaTitle()) {
                array_push($blogTitle, $category->getMetaTitle());
            } else {
                array_push($blogTitle, ucfirst($category->getName()));
            }

            return $blogTitle;
        }

        return ucfirst($category->getName());
    }
}
