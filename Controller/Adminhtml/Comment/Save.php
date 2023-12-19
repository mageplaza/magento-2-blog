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

namespace Mageplaza\Blog\Controller\Adminhtml\Comment;

use Exception;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Controller\Adminhtml\Comment;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\Comment
 */
class Save extends Comment
{
    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('comment')) {
            /** @var \Mageplaza\Blog\Model\Comment $post */
            $comment = $this->initComment();

            $this->prepareData($comment, $data);

            $this->_eventManager->dispatch(
                'mageplaza_blog_comment_prepare_save',
                ['comment' => $comment, 'request' => $this->getRequest()]
            );

            try {
                $comment->save();

                $this->messageManager->addSuccessMessage(__('The comment has been saved.'));
                $this->_getSession()->setData('mageplaza_blog_comment_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $comment->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('mageplaza_blog/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Comment.'));
            }

            $this->_getSession()->setData('mageplaza_blog_comment_data', $data);

            $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $comment->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('mageplaza_blog/*/');

        return $resultRedirect;
    }

    /**
     * @param $comment
     * @param array $data
     *
     * @return $this
     */
    protected function prepareData($comment, $data = [])
    {
        $comment->addData($data);

        return $this;
    }
}
