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
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Blog\Controller\Adminhtml\Topic;

abstract class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * JSON Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
	public $jsonFactory;

    /**
     * Topic Factory
     *
     * @var \Mageplaza\Blog\Model\TopicFactory
     */
	public $topicFactory;

    /**
     * constructor
     *
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Mageplaza\Blog\Model\TopicFactory $topicFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Mageplaza\Blog\Model\TopicFactory $topicFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
    
        $this->jsonFactory  = $jsonFactory;
        $this->topicFactory = $topicFactory;
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
        $postItems = $this->getRequest()->getParam('items', []);

        if (!($this->getRequest()->getParam('isAjax') && !empty($postItems))) {
            return $resultJson->setData([
                'messages' => [__('Please correct the data sent.')],
                'error' => true,
            ]);
        }

		$key = array_keys($postItems);
		$topicId = !empty($key) ? (int) $key[0] : '';
		/** @var \Mageplaza\Blog\Model\Topic $topic */
		$topic = $this->topicFactory->create()->load($topicId);
		try {
			$topicData = $postItems[$topicId];
			$topic->addData($topicData);
			$topic->save();
		} catch (\Magento\Framework\Exception\LocalizedException $e) {
			$messages[] = $this->getErrorWithTopicId($topic, $e->getMessage());
			$error = true;
		} catch (\RuntimeException $e) {
			$messages[] = $this->getErrorWithTopicId($topic, $e->getMessage());
			$error = true;
		} catch (\Exception $e) {
			$messages[] = $this->getErrorWithTopicId(
				$topic,
				__('Something went wrong while saving the Topic.')
			);
			$error = true;
		}

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * Add Topic id to error message
     *
     * @param \Mageplaza\Blog\Model\Topic $topic
     * @param string $errorText
     * @return string
     */
	public function getErrorWithTopicId(\Mageplaza\Blog\Model\Topic $topic, $errorText)
    {
        return '[Topic ID: ' . $topic->getId() . '] ' . $errorText;
    }
}
