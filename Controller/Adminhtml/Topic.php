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
 *                     @category  Mageplaza
 *                     @package   Mageplaza_Blog
 *                     @copyright Copyright (c) 2016
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Controller\Adminhtml;

abstract class Topic extends \Magento\Backend\App\Action
{
    /**
     * Topic Factory
     *
     * @var \Mageplaza\Blog\Model\TopicFactory
     */
    protected $topicFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Result redirect factory
     *
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

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
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
    
        $this->topicFactory          = $topicFactory;
        $this->coreRegistry          = $coreRegistry;
        $this->resultRedirectFactory = $resultRedirectFactory;
        parent::__construct($context);
    }

    /**
     * Init Topic
     *
     * @return \Mageplaza\Blog\Model\Topic
     */
    protected function initTopic()
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
