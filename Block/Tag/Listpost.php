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

namespace Mageplaza\Blog\Block\Tag;

use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Listpost
 * @package Mageplaza\Blog\Block\Tag
 */
class Listpost extends Frontend
{
    protected $_tag;

    /**
     * Override this function to apply collection for each type
     *
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    protected function getCollection()
    {
        if ($tag = $this->getBlogObject()) {
            return $this->helperData->getPostCollection(Data::TYPE_TAG, $tag->getId());
        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function getBlogObject()
    {
        if (!$this->_tag) {
            $id = $this->getRequest()->getParam('id');

            if ($id) {
                $tag = $this->helperData->getObjectByParam($id, null, Data::TYPE_TAG);
                if ($tag && $tag->getId()) {
                    $this->_tag = $tag;
                }
            }
        }

        return $this->_tag;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $tag = $this->getBlogObject();
            if ($tag) {
                $breadcrumbs->addCrumb($tag->getUrlKey(), [
                        'label' => __('Tag'),
                        'title' => __('Tag')
                    ]
                );
            }
        }
    }

    /**
     * @param bool $meta
     * @return array
     */
    public function getBlogTitle($meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);
        $tag  = $this->getBlogObject();
        if (!$tag) {
            return $blogTitle;
        }

        if ($meta) {
            if($title = $tag->getMetaTitle()) {
                $blogTitle = [$title];
            }
        } else {
            $blogTitle = $tag->getName();
        }

        return $blogTitle;
    }
}
