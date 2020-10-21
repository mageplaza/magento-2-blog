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

namespace Mageplaza\Blog\Controller\Adminhtml\Tag;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Model\Tag;
use Mageplaza\Blog\Model\TagFactory;
use RuntimeException;

/**
 * Class InlineEdit
 * @package Mageplaza\Blog\Controller\Adminhtml\Tag
 */
class InlineEdit extends Action
{
    /**
     * @var JsonFactory
     */
    public $jsonFactory;

    /**
     * @var TagFactory
     */
    public $tagFactory;

    /**
     * InlineEdit constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param TagFactory $tagFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        TagFactory $tagFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->tagFactory = $tagFactory;

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
        $postItems = $this->getRequest()->getParam('items', []);
        if (!($this->getRequest()->getParam('isAjax') && !empty($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

        $key = array_keys($postItems);
        $tagId = !empty($key) ? (int)$key[0] : '';
        /** @var Tag $tag */
        $tag = $this->tagFactory->create()->load($tagId);
        try {
            $tag->addData($postItems[$tagId])
                ->save();
        } catch (LocalizedException $e) {
            $messages[] = $this->getErrorWithTagId($tag, $e->getMessage());
            $error = true;
        } catch (RuntimeException $e) {
            $messages[] = $this->getErrorWithTagId($tag, $e->getMessage());
            $error = true;
        } catch (Exception $e) {
            $messages[] = $this->getErrorWithTagId($tag, __('Something went wrong while saving the Tag.'));
            $error = true;
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Tag id to error message
     *
     * @param Tag $tag
     * @param string $errorText
     *
     * @return string
     */
    public function getErrorWithTagId(Tag $tag, $errorText)
    {
        return '[Tag ID: ' . $tag->getId() . '] ' . $errorText;
    }
}
