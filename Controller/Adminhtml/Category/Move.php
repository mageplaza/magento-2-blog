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

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Messages;
use Magento\Framework\View\LayoutFactory;
use Mageplaza\Blog\Controller\Adminhtml\Category;
use Mageplaza\Blog\Model\CategoryFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Move
 * @package Mageplaza\Blog\Controller\Adminhtml\Category
 */
class Move extends Category
{
    /**
     * JSON Result Factory
     *
     * @var JsonFactory
     */
    public $resultJsonFactory;

    /**
     * Layout Factory
     *
     * @var LayoutFactory
     */
    public $layoutFactory;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * Move constructor.
     *
     * @param JsonFactory $resultJsonFactory
     * @param LayoutFactory $layoutFactory
     * @param LoggerInterface $logger
     * @param CategoryFactory $categoryFactory
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        CategoryFactory $categoryFactory,
        JsonFactory $resultJsonFactory,
        LayoutFactory $layoutFactory,
        LoggerInterface $logger
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory = $layoutFactory;
        $this->logger = $logger;

        parent::__construct($context, $coreRegistry, $categoryFactory);
    }

    /**
     * @return Json
     */
    public function execute()
    {
        /** New parent Blog category identifier */
        $parentNodeId = $this->getRequest()->getPost('pid', false);

        /** Blog category id after which we have put our Blog category */
        $prevNodeId = $this->getRequest()->getPost('aid', false);

        /** @var $block Messages */
        $block = $this->layoutFactory->create()->getMessagesBlock();
        $error = false;

        try {
            $category = $this->initCategory();
            if ($category !== false) {
                $category->move($parentNodeId, $prevNodeId);
            } else {
                $error = true;
                $this->messageManager->addErrorMessage(__('There was a Blog category move error.'));
            }
        } catch (LocalizedException $e) {
            $error = true;
            $this->messageManager->addErrorMessage(__('There was a Blog category move error.'));
        } catch (Exception $e) {
            $error = true;
            $this->messageManager->addErrorMessage(__('There was a Blog category move error.'));
            $this->logger->critical($e);
        }

        if (!$error) {
            $this->messageManager->addSuccessMessage(__('You moved the Blog category'));
        }

        $block->setMessages($this->messageManager->getMessages(true));
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData([
            'messages' => $block->getGroupedHtml(),
            'error' => $error
        ]);

        return $resultJson;
    }
}
