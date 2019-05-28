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

use Mageplaza\Blog\Helper\Data;

/**
 * Class Listpost
 * @package Mageplaza\Blog\Block\Category
 */
class Listpost extends \Mageplaza\Blog\Block\Listpost
{
    /**
     * @var string
     */
    protected $_category;

    /**
     * Override this function to apply collection for each type
     *
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    protected function getCollection()
    {
        if ($category = $this->getBlogObject()) {
            return $this->helperData->getPostCollection(Data::TYPE_CATEGORY, $category->getId());
        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function getBlogObject()
    {
        if (!$this->_category) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $category = $this->helperData->getObjectByParam($id, null, Data::TYPE_CATEGORY);
                if ($category && $category->getId()) {
                    $this->_category = $category;
                }
            }
        }

        return $this->_category;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $category = $this->getBlogObject();
            if ($category) {
                $breadcrumbs->addCrumb($category->getUrlKey(), [
                    'label' => $this->getBlogTitle(),
                    'title' => $this->getBlogTitle()
                ]);
            }
        }
    }

    /**
     * @param bool $meta
     *
     * @return array
     */
    public function getBlogTitle($meta = false)
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
