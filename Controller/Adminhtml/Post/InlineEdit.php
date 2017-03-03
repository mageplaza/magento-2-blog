<?php
/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 * @category  Mageplaza
 * @package   Mageplaza_Blog
 * @copyright Copyright (c) 2016
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Controller\Adminhtml\Post;

abstract class InlineEdit extends \Magento\Backend\App\Action
{
    /**
     * JSON Factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $jsonFactory;

    /**
     * Post Factory
     *
     * @var \Mageplaza\Blog\Model\PostFactory
     */
    private $postFactory;

    /**
     * constructor
     *
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Mageplaza\Blog\Model\PostFactory $postFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Mageplaza\Blog\Model\PostFactory $postFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
    
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
		$postId = !empty($key) ? (int) $key[0] : '';
		/** @var \Mageplaza\Blog\Model\Post $post */
		$post = $this->postFactory->create()->load($postId);
		try {
			$postData = $postItems[$postId];
			$post->addData($postData);
			$post->save();
		} catch (\Magento\Framework\Exception\LocalizedException $e) {
			$messages[] = $this->getErrorWithPostId($post, $e->getMessage());
			$error = true;
		} catch (\RuntimeException $e) {
			$messages[] = $this->getErrorWithPostId($post, $e->getMessage());
			$error = true;
		} catch (\Exception $e) {
			$messages[] = $this->getErrorWithPostId(
				$post,
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
     * Add Post id to error message
     *
     * @param \Mageplaza\Blog\Model\Post $post
     * @param string $errorText
     * @return string
     */
    private function getErrorWithPostId(\Mageplaza\Blog\Model\Post $post, $errorText)
    {
        return '[Post ID: ' . $post->getId() . '] ' . $errorText;
    }
}
