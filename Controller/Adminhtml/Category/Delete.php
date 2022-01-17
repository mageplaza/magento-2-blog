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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\Blog\Controller\Adminhtml\Category;

/**
 * Class Delete
 * @package Mageplaza\Blog\Controller\Adminhtml\Category
 */
class Delete extends Category
{
    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $categoryFactory = $this->categoryFactory->create();
                if ($id !== 1) {
                    $parentCategoryCollection = $categoryFactory->getCollection()
                        ->addFieldToFilter('category_id', ['eq' => $id])
                        ->addFieldToFilter('parent_id', ['eq' => 1]);
                    if ($parentCategoryCollection->getSize()) {
                        $pathCategory = $categoryFactory->load($id)->getPath();
                        $collections  = $categoryFactory->getCollection()
                            ->addFieldToFilter('path', ['like' => $pathCategory . '%']);
                        foreach ($collections as $collection) {
                            $collection->delete();
                        }
                    } else {
                        $categoryFactory->load($id)->delete();
                    }

                    $this->messageManager->addSuccessMessage(__('The Blog Category has been deleted.'));

                    $resultRedirect->setPath('mageplaza_blog/*/');

                    return $resultRedirect;
                }
            } catch (Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $id]);

                return $resultRedirect;
            }
        }

        // display error message
        $this->messageManager->addErrorMessage(__('Blog Category to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('mageplaza_blog/*/');

        return $resultRedirect;
    }
}
