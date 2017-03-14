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
namespace Mageplaza\Blog\Controller\Adminhtml;

abstract class Topic extends \Magento\Backend\App\Action
{
    /**
     * Topic Factory
     *
     * @var \Mageplaza\Blog\Model\TopicFactory
     */
	public $topicFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
	public $coreRegistry;

    /**
     * Result redirect factory
     *
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
	public $resultRedirectFactory;

    /**
     * constructor
     *
     * @param \Mageplaza\Blog\Model\TopicFactory $topicFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mageplaza\Blog\Model\TopicFactory $topicFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context
    ) {
    
        $this->topicFactory          = $topicFactory;
        $this->coreRegistry          = $coreRegistry;
        $this->resultRedirectFactory = $context->getRedirect();
        parent::__construct($context);
    }

    /**
     * Init Topic
     *
     * @return \Mageplaza\Blog\Model\Topic
     */
	public function initTopic()
    {
        $topicId  = (int) $this->getRequest()->getParam('topic_id');
        /** @var \Mageplaza\Blog\Model\Topic $topic */
        $topic    = $this->topicFactory->create();
        if ($topicId) {
            $topic->load($topicId);
        }
        $this->coreRegistry->register('mageplaza_blog_topic', $topic);
        return $topic;
    }
}
