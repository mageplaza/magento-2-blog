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

namespace Mageplaza\Blog\Controller\Adminhtml\Author;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Model\Author;
use Mageplaza\Blog\Model\AuthorFactory;
use Mageplaza\Blog\Model\Post;
use RuntimeException;

/**
 * Class InlineEdit
 * @package Mageplaza\Blog\Controller\Adminhtml\Author
 */
class InlineEdit extends Action
{
    /**
     * JSON Factory
     *
     * @var JsonFactory
     */
    public $jsonFactory;

    /**
     * Author Factory
     *
     * @var AuthorFactory
     */
    public $authorFactory;

    /**
     * InlineEdit constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param AuthorFactory $postFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        AuthorFactory $postFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->authorFactory = $postFactory;

        parent::__construct($context);
    }

    /**
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $error = false;
        $messages = [];
        $authorItems = $this->getRequest()->getParam('items', []);
        if (!(!empty($authorItems) && $this->getRequest()->getParam('isAjax'))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        $key = array_keys($authorItems);
        $authorId = !empty($key) ? (int)$key[0] : '';
        /** @var Post $post */
        $author = $this->authorFactory->create()->load($authorId);
        try {
            $authorData = $authorItems[$authorId];
            $author->addData($authorData);
            $author->save();
        } catch (LocalizedException $e) {
            $messages[] = $this->getErrorWithPostId($post, $e->getMessage());
            $error = true;
        } catch (RuntimeException $e) {
            $messages[] = $this->getErrorWithPostId($post, $e->getMessage());
            $error = true;
        } catch (Exception $e) {
            $messages[] = $this->getErrorWithPostId(
                $author,
                __('Something went wrong while saving the Post.')
            );
            $error = true;
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param Author $author
     * @param $errorText
     *
     * @return string
     */
    public function getErrorWithPostId(Author $author, $errorText)
    {
        return '[Post ID: ' . $author->getId() . '] ' . $errorText;
    }
}
