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

use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Block\Frontend;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Data as DataHelper;
use Mageplaza\Blog\Model\CommentFactory;
use Mageplaza\Blog\Model\LikeFactory;

/**
 * Class Widget
 * @package Mageplaza\Blog\Block\MonthlyArchive
 */
class Widget extends Frontend
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * @var array
     */
    protected $_postDate;

    /**
     * Widget constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Mageplaza\Blog\Model\CommentFactory $commentFactory
     * @param \Mageplaza\Blog\Model\LikeFactory $likeFactory
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Mageplaza\Blog\Helper\Data $helperData
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param array $data
     */
    public function __construct(
        Context $context,
        FilterProvider $filterProvider,
        CommentFactory $commentFactory,
        LikeFactory $likeFactory,
        CustomerRepositoryInterface $customerRepository,
        DataHelper $helperData,
        DateTime $dateTime,
        array $data = []
    )
    {
        $this->dateTime = $dateTime;

        parent::__construct($context, $filterProvider, $commentFactory, $likeFactory, $customerRepository, $helperData, $data);
    }

    /**
     * @return mixed
     */
    public function isEnable()
    {
        return $this->helperData->getBlogConfig('monthly_archive/enable_monthly');
    }

    /**
     * @return array
     */
    public function getDateArrayCount()
    {
        return array_values(array_count_values($this->getDateArray()));
    }

    /**
     * @return array
     */
    public function getDateArrayUnique()
    {
        return array_values(array_unique($this->getDateArray()));
    }

    /**
     * get array of posts's date formatted
     * @return array
     */
    public function getDateArray()
    {
        $dateArray = [];
        foreach ($this->getPostDate() as $postDate) {
            $dateArray[] = date("F Y", $this->dateTime->timestamp($postDate));
        }

        return $dateArray;
    }

    /**
     * @return array
     */
    protected function getPostDate()
    {
        if (!$this->_postDate) {
            $posts     = $this->helperData->getPostList();
            $postDates = [];
            if ($posts->getSize()) {
                foreach ($posts as $post) {
                    $postDates[] = $post->getPublishDate();
                }
            }
            $this->_postDate = $postDates;
        }

        return $this->_postDate;
    }

    /**
     * @return int|mixed
     */
    public function getDateCount()
    {
        $limit          = $this->helperData->getBlogConfig('monthly_archive/number_records') ?: 5;
        $dateArrayCount = $this->getDateArrayCount();
        $count          = count($dateArrayCount);
        $result         = ($count < $limit) ? $count : $limit;

        return $result;
    }

    /**
     * @param $month
     * @return string
     */
    public function getMonthlyUrl($month)
    {
        return $this->helperData->getBlogUrl($month, Data::TYPE_MONTHLY);
    }

    /**
     * @return array
     */
    public function getDateLabel()
    {
        $postDates      = $this->getPostDate();
        $postDatesLabel = [];
        if (sizeof($postDates)) {
            foreach ($postDates as $date) {
                $postDatesLabel[] = $this->helperData->getDateFormat($date, true);
            }
        }
        $result = array_values(array_unique($postDatesLabel));

        return $result;
    }
}
