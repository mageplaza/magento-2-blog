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

namespace Mageplaza\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\CategoryFactory;

/**
 * Class Category
 * @package Mageplaza\Blog\Controller\Adminhtml
 */
abstract class Category extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_Blog::category';

    /**
     * Blog Category Factory
     *
     * @var \Mageplaza\Blog\Model\CategoryFactory
     */
    public $categoryFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * Category constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CategoryFactory $categoryFactory
    )
    {
        $this->categoryFactory = $categoryFactory;
        $this->coreRegistry    = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @param bool $register
     * @return bool|\Mageplaza\Blog\Model\Category
     */
    public function initCategory($register = false)
    {
        $categoryId = (int)$this->getRequest()->getParam('id');

        /** @var \Mageplaza\Blog\Model\Post $post */
        $category = $this->categoryFactory->create();
        if ($categoryId) {
            $category->load($categoryId);
            if (!$category->getId()) {
                $this->messageManager->addErrorMessage(__('This category no longer exists.'));

                return false;
            }
        }

        if ($register) {
            $this->coreRegistry->register('category', $category);
        }

        return $category;
    }
}
