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
 * Class Tags
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class Tags extends Post
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    public $resultLayoutFactory;

    /**
     * Tags constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Mageplaza\Blog\Model\PostFactory $tagFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        LayoutFactory $resultLayoutFactory,
        PostFactory $tagFactory
    )
    {
        $this->resultLayoutFactory = $resultLayoutFactory;

        parent::__construct($tagFactory, $registry, $context);
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initPost(true);

        return $this->resultLayoutFactory->create();
    }
}
