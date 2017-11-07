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

namespace Mageplaza\Blog\Controller\Adminhtml\Topic;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\Blog\Controller\Adminhtml\Topic;
use Mageplaza\Blog\Model\TopicFactory;

/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\Topic
 */
class Save extends Topic
{
    /**
     * JS helper
     *
     * @var \Magento\Backend\Helper\Js
     */
    public $jsHelper;

    /**
     * Save constructor.
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Mageplaza\Blog\Model\TopicFactory $topicFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Js $jsHelper,
        TopicFactory $topicFactory
    )
    {
        $this->jsHelper = $jsHelper;

        parent::__construct($context, $registry, $topicFactory);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPost('topic')) {
            /** @var \Mageplaza\Blog\Model\Topic $topic */
            $topic = $this->initTopic();

            $topic->setData($data);

            if ($posts = $this->getRequest()->getPost('posts', false)) {
                $topic->setPostsData($this->jsHelper->decodeGridSerializedInput($posts));
            }

            try {
                $topic->save();

                $this->messageManager->addSuccess(__('The Topic has been saved.'));
                $this->_getSession()->setData('mageplaza_blog_topic_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $topic->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('mageplaza_blog/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Topic.'));
            }

            $this->_getSession()->setData('mageplaza_blog_topic_data', $data);

            $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $topic->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('mageplaza_blog/*/');

        return $resultRedirect;
    }
}
