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

namespace Mageplaza\Blog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Mageplaza\Blog\Controller\Adminhtml\Category;
use Mageplaza\Blog\Model\CategoryFactory;

/**
 * Class RefreshPath
 * @package Mageplaza\Blog\Controller\Adminhtml\Category
 */
class RefreshPath extends Category
{
    /**
     * JSON Result Factory
     *
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * RefreshPath constructor.
     *
     * @param JsonFactory $resultJsonFactory
     * @param CategoryFactory $categoryFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param \Mageplaza\Blog\Model\ResourceModel\Category $categoryResource
     * @param \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CategoryFactory $categoryFactory,
        JsonFactory $resultJsonFactory,
        \Mageplaza\Blog\Model\ResourceModel\Category $categoryResource,
        \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context, $coreRegistry, $categoryFactory, $categoryResource, $categoryCollectionFactory);
    }

    /**
     * Build response for refresh input element 'path' in form
     *
     * @return Json|null
     */
    public function execute()
    {
        $categoryId = (int)$this->getRequest()->getParam('id');
        if ($categoryId) {
            $category = $this->categoryFactory->create();
            $this->categoryResource->load($category, $categoryId);

            /** @var Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();

            return $resultJson->setData(['id' => $categoryId, 'path' => $category->getPath()]);
        }
    }
}
