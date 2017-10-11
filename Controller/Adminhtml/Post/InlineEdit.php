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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Model\PostFactory;

/**
 * Class InlineEdit
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
abstract class InlineEdit extends Action
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
     * @var \Mageplaza\Blog\Model\PostFactory
     */
    public $postFactory;

    /**
     * InlineEdit constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Mageplaza\Blog\Model\PostFactory $postFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        PostFactory $postFactory
    )
    {
        $this->jsonFactory = $jsonFactory;
        $this->postFactory = $postFactory;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error      = false;
        $messages   = [];
        $postItems  = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && !empty($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error'    => true,
            ]);
        }

        $key    = array_keys($postItems);
        $postId = !empty($key) ? (int)$key[0] : '';
        /** @var \Mageplaza\Blog\Model\Post $post */
        $post = $this->postFactory->create()->load($postId);
        try {
            $postData = $postItems[$postId];
            $post->addData($postData);
            $post->save();
        } catch (LocalizedException $e) {
            $messages[] = $this->getErrorWithPostId($post, $e->getMessage());
            $error      = true;
        } catch (\RuntimeException $e) {
            $messages[] = $this->getErrorWithPostId($post, $e->getMessage());
            $error      = true;
        } catch (\Exception $e) {
            $messages[] = $this->getErrorWithPostId(
                $post,
                __('Something went wrong while saving the Post.')
            );
            $error      = true;
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error'    => $error
        ]);
    }

    /**
     * Add Post id to error message
     *
     * @param \Mageplaza\Blog\Model\Post $post
     * @param string $errorText
     * @return string
     */
    public function getErrorWithPostId(\Mageplaza\Blog\Model\Post $post, $errorText)
    {
        return '[Post ID: ' . $post->getId() . '] ' . $errorText;
    }
}
