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
namespace Mageplaza\Blog\Controller\Adminhtml\Author;

class Edit extends \Mageplaza\Blog\Controller\Adminhtml\Author
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
	protected $authSession;

	/**
	 * constructor
	 *
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	 * @param \Mageplaza\Blog\Model\AuthorFactory $authorFactory
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Backend\App\Action\Context $context
	 * @internal param \Magento\Backend\Model\Session $backendSession
	 * @internal param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
	 */
	public function __construct(
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
		\Mageplaza\Blog\Model\AuthorFactory $authorFactory,
		\Magento\Framework\Registry $registry,
		\Magento\Backend\Model\Auth\Session $authSession,
		\Magento\Backend\App\Action\Context $context
	) {

		$this->backendSession    = $context->getSession();
		$this->resultPageFactory = $resultPageFactory;
		$this->resultJsonFactory = $resultJsonFactory;
		$this->authSession = $authSession;
		parent::__construct($authorFactory, $registry, $context);
	}

	/**
	 * is action allowed
	 *
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Mageplaza_Blog::author');
	}

	/**
	 * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\View\Result\Page
	 */
	public function execute()
	{
		$user = $this->authSession->getUser();
		$userFullname = $user->getFirstName(). ' ' . $user->getLastName();
		$id = $user->getId();
		$authors = $this->initAuthor()->load($id);
		if(!$authors->getId()) {
			$authors->setData(['user_id' => $id])->save();
		}
		/** @var \Magento\Backend\Model\View\Result\Page|\Magento\Framework\View\Result\Page $resultPage */
		$resultPage = $this->resultPageFactory->create();
		$resultPage->setActiveMenu('Mageplaza_Blog::author');
		$resultPage->getConfig()->getTitle()->set(__('Author Management'));

		$authors->load($id);
		$title = $userFullname;
		$resultPage->getConfig()->getTitle()->prepend($title);
		$data = $this->backendSession->getData('mageplaza_blog_author_data', true);

		if (!empty($data)) {
			$authors->setData($data);
		}
		return $resultPage;
	}
}
