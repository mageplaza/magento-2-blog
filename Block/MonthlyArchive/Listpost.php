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

namespace Mageplaza\Blog\Block\MonthlyArchive;

use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Listpost
 * @package Mageplaza\Blog\Block\MonthlyArchive
 */
class Listpost extends Frontend
{
    /**
     * Override this function to apply collection for each type
     *
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    protected function getCollection()
    {
        return $this->helperData->getPostCollection(Data::TYPE_MONTHLY, $this->getMonthKey());
    }

    /**
     * @return mixed
     */
    protected function getMonthKey()
    {
        return $this->getRequest()->getParam('month_key');
    }

    /**
     * @return false|string
     */
    protected function getMonthLabel()
    {
        return $this->helperData->getDateFormat($this->getMonthKey(), true);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb($this->getMonthKey(), [
                    'label' => __('Monthy Archive'),
                    'title' => __('Monthy Archive')
                ]
            );
        }
    }

    /**
     * @param bool $meta
     * @return array
     */
    public function getBlogTitle($meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);
        if ($meta) {
            $blogTitle[] = $this->getMonthLabel();
        }

        return $blogTitle;
    }
}
