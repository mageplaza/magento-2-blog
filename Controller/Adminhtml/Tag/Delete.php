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

namespace Mageplaza\Blog\Controller\Adminhtml\Tag;

use Mageplaza\Blog\Controller\Adminhtml\Tag;

/**
 * Class Delete
 * @package Mageplaza\Blog\Controller\Adminhtml\Tag
 */
class Delete extends Tag
{
    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $this->tagFactory->create()
                    ->load($id)
                    ->delete();
                $this->messageManager->addSuccess(__('The Tag has been deleted.'));
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $id]);

                return $resultRedirect;
            }
        } else {
            // display error message
            $this->messageManager->addError(__('Tag to delete was not found.'));
        }

        // go to grid
        $resultRedirect->setPath('mageplaza_blog/*/');

        return $resultRedirect;
    }
}
