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

namespace Mageplaza\Blog\Block\MonthlyArchive;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;

/**
 * Class ListPost
 * @package Mageplaza\Blog\Block\MonthlyArchive
 */
class ListPost extends \Mageplaza\Blog\Block\ListPost
{
    /**
     * @return Collection|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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
     * @throws \Exception
     */
    protected function getMonthLabel()
    {
        return $this->helperData->getDateFormat($this->getMonthKey() . '-10', true);
    }

    /**
     * @return void
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        // month_key is missing when this block renders through a Varnish ESI
        // sub-request (its own request carries no route params), which made
        // addCrumb() use null as the array key -- deprecated in PHP 8.5, a
        // TypeError on newer PHP.
        $monthKey = $this->getMonthKey();
        if ($monthKey && ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs'))) {
            $breadcrumbs->addCrumb($monthKey, [
                'label' => __('Monthy Archive'),
                'title' => __('Monthy Archive')
            ]);
        }
    }

    /**
     * @param bool $meta
     *
     * @return array|false|string
     */
    public function getBlogTitle($meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);

        if ($meta) {
            array_push($blogTitle, $this->getMonthLabel());

            return $blogTitle;
        }

        return $this->getMonthLabel();
    }
}
