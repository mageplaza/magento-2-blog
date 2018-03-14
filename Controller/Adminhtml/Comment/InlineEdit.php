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
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Controller\Adminhtml\Comment;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Model\CommentFactory;

/**
 * Class InlineEdit
 * @package Mageplaza\Blog\Controller\Adminhtml\Comment
 */
class InlineEdit extends Action
{
    /**
     * JSON Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    public $jsonFactory;

    /**
     * Post Factory
     *
     * @var \Mageplaza\Blog\Model\CommentFactory
     */
    public $commentFactory;

    /**
     * InlineEdit constructor.
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param CommentFactory $commentFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        CommentFactory $commentFactory
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->commentFactory = $commentFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $commentItems = $this->getRequest()->getParam('items', []);

        if (!($this->getRequest()->getParam('isAjax') && !empty($commentItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        $key = array_keys($commentItems);
        $commentId = !empty($key) ? (int)$key[0] : '';

        /** @var \Mageplaza\Blog\Model\Post $post */
        $comment = $this->commentFactory->create()->load($commentId);
        try {
            $commentData = $commentItems[$commentId];
            $comment->addData($commentData);
            $comment->save();
        } catch (LocalizedException $e) {
            $messages[] = $this->getErrorWithCommentId($comment, $e->getMessage());
            $error = true;
        } catch (\RuntimeException $e) {
            $messages[] = $this->getErrorWithCommentId($comment, $e->getMessage());
            $error = true;
        } catch (\Exception $e) {
            $messages[] = $this->getErrorWithCommentId(
                $comment,
                __('Something went wrong while saving the Comment.')
            );
            $error = true;
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param \Mageplaza\Blog\Model\Comment $comment
     * @param $errorText
     * @return string
     */
    public function getErrorWithCommentId(\Mageplaza\Blog\Model\Comment $comment, $errorText)
    {
        return '[Comment ID: ' . $comment->getId() . '] ' . $errorText;
    }
}
