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

namespace Mageplaza\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\TopicFactory;

/**
 * Class Topic
 * @package Mageplaza\Blog\Controller\Adminhtml
 */
abstract class Topic extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_Blog::topic';

    /**
     * Topic Factory
     *
     * @var TopicFactory
     */
    public $topicFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * Topic constructor.
     *
     * @param TopicFactory $topicFactory
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        TopicFactory $topicFactory
    ) {
        $this->topicFactory = $topicFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @param bool $register
     *
     * @return bool|\Mageplaza\Blog\Model\Topic
     */
    protected function initTopic($register = false)
    {
        $topicId = (int)$this->getRequest()->getParam('id');

        /** @var \Mageplaza\Blog\Model\Topic $topic */
        $topic = $this->topicFactory->create();
        if ($topicId) {
            $topic->load($topicId);
            if (!$topic->getId()) {
                $this->messageManager->addErrorMessage(__('This topic no longer exists.'));

                return false;
            }
        }

        if ($register) {
            $this->coreRegistry->register('mageplaza_blog_topic', $topic);
        }

        return $topic;
    }
}
