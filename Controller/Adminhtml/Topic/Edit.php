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

class Edit extends \Mageplaza\Blog\Controller\Adminhtml\Topic
{
    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
	public $backendSession;

    /**
     * Page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
	public $resultPageFactory;

    /**
     * Result JSON factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
	public $resultJsonFactory;

    /**
     * constructor
     *
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Mageplaza\Blog\Model\TopicFactory $topicFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Mageplaza\Blog\Model\TopicFactory $topicFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
    
        $this->backendSession    = $context->getSession();
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($topicFactory, $registry, $context);
    }

    /**
     * is action allowed
     *
     * @return bool
     */
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mageplaza_Blog::topic');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('topic_id');
        /** @var \Mageplaza\Blog\Model\Topic $topic */
        $topic = $this->initTopic();
        /** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_Blog::topic');
        $resultPage->getConfig()->getTitle()->set(__('Topics'));
        if ($id) {
            $topic->load($id);
            if (!$topic->getId()) {
                $this->messageManager->addError(__('This Topic no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath(
                    'mageplaza_blog/*/edit',
                    [
                        'topic_id' => $topic->getId(),
                        '_current' => true
                    ]
                );
                return $resultRedirect;
            }
        }
        $title = $topic->getId() ? $topic->getName() : __('New Topic');
        $resultPage->getConfig()->getTitle()->prepend($title);
        $data = $this->backendSession->getData('mageplaza_blog_topic_data', true);
        if (!empty($data)) {
            $topic->setData($data);
        }
        return $resultPage;
    }
}
