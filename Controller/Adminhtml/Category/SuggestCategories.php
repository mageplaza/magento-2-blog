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
use Magento\Framework\View\LayoutFactory;
use Mageplaza\Blog\Block\Adminhtml\Category\Tree;
use Mageplaza\Blog\Controller\Adminhtml\Category;
use Mageplaza\Blog\Model\CategoryFactory;

/**
 * Class SuggestCategories
 * @package Mageplaza\Blog\Controller\Adminhtml\Category
 */
class SuggestCategories extends Category
{
    /**
     * Json result factory
     *
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * Layout factory
     *
     * @var LayoutFactory
     */
    public $layoutFactory;

    /**
     * SuggestCategories constructor.
     *
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     * @param CategoryFactory $categoryFactory
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CategoryFactory $categoryFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory     = $layoutFactory;

        parent::__construct($context, $coreRegistry, $categoryFactory);
    }

    /**
     * Blog Category list suggestion based on already entered symbols
     *
     * @return Json
     */
    public function execute()
    {
        /** @var Tree $treeBlock */
        $treeBlock = $this->layoutFactory->create()->createBlock('Mageplaza\Blog\Block\Adminhtml\Category\Tree');
        $data      = $treeBlock->getSuggestedCategoriesJson($this->getRequest()->getParam('label_part'));

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setJsonData($data);

        return $resultJson;
    }
}
