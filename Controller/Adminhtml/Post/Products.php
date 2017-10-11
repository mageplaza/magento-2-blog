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

namespace Mageplaza\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\Blog\Controller\Adminhtml\Post;
use Mageplaza\Blog\Model\PostFactory;

/**
 * Class Products
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class Products extends Post
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $resultLayoutFactory;

    /**
     * Products constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Mageplaza\Blog\Model\PostFactory $productFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PostFactory $productFactory,
        LayoutFactory $resultLayoutFactory
    )
    {
        parent::__construct($productFactory, $registry, $context);

        $this->resultLayoutFactory = $resultLayoutFactory;
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->initPost(true);

        return $this->resultLayoutFactory->create();
    }
}
