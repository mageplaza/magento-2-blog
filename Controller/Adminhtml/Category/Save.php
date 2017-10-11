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

namespace Mageplaza\Blog\Controller\Adminhtml\Category;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Mageplaza\Blog\Controller\Adminhtml\Category;
use Mageplaza\Blog\Model\CategoryFactory;

/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\Category
 */
class Save extends Category
{
    /**
     * Result Raw Factory
     *
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    public $resultRawFactory;

    /**
     * Result Json Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $resultJsonFactory;

    /**
     * Layout Factory
     *
     * @var \Magento\Framework\View\LayoutFactory
     */
    public $layoutFactory;

    /**
     * JS helper
     *
     * @var \Magento\Backend\Helper\Js
     */
    public $jsHelper;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Backend\Helper\Js $jsHelper
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CategoryFactory $categoryFactory,
        RawFactory $resultRawFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        Js $jsHelper
    )
    {
        $this->resultRawFactory  = $resultRawFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory     = $layoutFactory;
        $this->jsHelper          = $jsHelper;

        parent::__construct($context, $coreRegistry, $categoryFactory);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPost('category')) {
            $category = $this->initCategory();
            if (!$category) {
                $resultRedirect->setPath('mageplaza_blog/*/', ['_current' => true]);

                return $resultRedirect;
            }

            $category->addData($data);
            if ($posts = $this->getRequest()->getPost('category_posts')) {
                $posts = json_decode($posts, true);
                $category->setPostsData($posts);
            }

            if (!$category->getId()) {
                $parentId = $this->getRequest()->getParam('parent');
                if (!$parentId) {
                    $parentId = \Mageplaza\Blog\Model\Category::TREE_ROOT_ID;
                }
                $parentCategory = $this->categoryFactory->create()->load($parentId);
                $category->setPath($parentCategory->getPath());
                $category->setParentId($parentId);
            }

            $this->_eventManager->dispatch(
                'mageplaza_blog_category_prepare_save',
                ['category' => $category, 'request' => $this->getRequest()]
            );

            try {
                $category->save();
                $this->messageManager->addSuccess(__('You saved the Blog Category.'));
                $this->_getSession()->setData('mageplaza_blog_category_data', false);
                $refreshTree = true;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_getSession()->setData('mageplaza_blog_category_data', $data);
                $refreshTree = false;
            }

            if ($this->getRequest()->getPost('return_session_messages_only')) {
                $category->load($category->getId());
                // to obtain truncated Blog Category Name
                /** @var $block \Magento\Framework\View\Element\Messages */
                $block = $this->layoutFactory->create()->getMessagesBlock();
                $block->setMessages($this->messageManager->getMessages(true));

                /** @var \Magento\Framework\Controller\Result\Json $resultJson */
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setData(
                    [
                        'messages' => $block->getGroupedHtml(),
                        'error'    => !$refreshTree,
                        'category' => $category->toArray(),
                    ]
                );

                return $resultJson;
            }

            $resultRedirect->setPath('mageplaza_blog/*/edit', ['_current' => true, 'category_id' => $category->getId()]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('mageplaza_blog/*/edit', ['_current' => true]);

        return $resultRedirect;
    }
}
