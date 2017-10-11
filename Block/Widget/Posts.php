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

namespace Mageplaza\Blog\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\PostFactory;

/**
 * Class Posts
 * @package Mageplaza\Blog\Block\Widget
 */
class Posts extends Template implements BlockInterface
{
    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    protected $helper;

    /**
     * @var \Mageplaza\Blog\Model\PostFactory
     */
    protected $postFactory;

    /**
     * @var \Mageplaza\Blog\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var string
     */
    protected $_template = "widget/posts.phtml";

    /**
     * Posts constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageplaza\Blog\Helper\Data $helperData
     * @param \Mageplaza\Blog\Model\PostFactory $postFactory
     * @param \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $helperData,
        PostFactory $postFactory,
        CategoryFactory $categoryFactory,
        array $data = []
    )
    {
        $this->helper          = $helperData;
        $this->postFactory     = $postFactory;
        $this->categoryFactory = $categoryFactory;

        parent::__construct($context, $data);
    }

    /**
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection|\Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getCollection()
    {
        if ($this->hasData('show_type') && $this->getData('show_type') === 'category') {
            $collection = $this->categoryFactory->create()
                ->load($this->getData('category_id'))
                ->getSelectedPostsCollection();
        } else {
            $collection = $this->postFactory->create()
                ->getCollection();
        }

        $collection->setOrder('publish_date')
            ->setPageSize($this->getData('post_count'));

        return $collection;
    }

    /**
     * @return \Mageplaza\Blog\Helper\Data
     */
    public function getHelperData()
    {
        return $this->helper;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->getData('title');
    }

    /**
     * @param $code
     * @return string
     */
    public function getBlogUrl($code)
    {
        return $this->helper->getBlogUrl($code);
    }
}
