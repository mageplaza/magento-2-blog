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
namespace Mageplaza\Blog\Controller\Adminhtml\Category;

class Edit extends \Mageplaza\Blog\Controller\Adminhtml\Category
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

    public $objectManager;

    public $dataObject;

    /**
     * constructor
     *
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
		\Magento\Framework\DataObject $dataObject,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Mageplaza\Blog\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Backend\App\Action\Context $context
    ) {
    	$this->dataObject = $dataObject;
        $this->backendSession    = $context->getSession();
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->objectManager = $context->getObjectManager();
        parent::__construct($categoryFactory, $registry, $context);
    }
	
    /**
     * Edit Blog category page
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $parentId = (int)$this->getRequest()->getParam('parent');
        $categoryId = (int)$this->getRequest()->getParam('category_id');

        $category = $this->initCategory();
        if (!$category) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('mageplaza_blog/*/', ['_current' => true, 'id' => null]);
        }

        /**
         * Check if we have data in session (if during Blog category save was exception)
         */
        $data = $this->_getSession()->getMageplazaBlogCategoryData(true);
        if (isset($data['category'])) {
            $category->addData($data['category']);
        }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        /**
         * Build response for ajax request
         */
        if ($this->getRequest()->getQuery('isAjax')) {
            // prepare breadcrumbs of selected Blog category, if any
            $breadcrumbsPath = $category->getPath();
            if (empty($breadcrumbsPath)) {
                // but if no Blog category, and it is deleted - prepare breadcrumbs from path, saved in session
                $breadcrumbsPath = $this->objectManager->get(
                    'Magento\Backend\Model\Auth\Session'
                )->getDeletedPath(
                    true
                );
                if (!empty($breadcrumbsPath)) {
                    $breadcrumbsPath = explode('/', $breadcrumbsPath);
                    // no need to get parent breadcrumbs if deleting Blog category level 1
                    if (count($breadcrumbsPath) <= 1) {
                        $breadcrumbsPath = '';
                    } else {
                        array_pop($breadcrumbsPath);
                        $breadcrumbsPath = implode('/', $breadcrumbsPath);
                    }
                }
            }

            $eventResponse = $this->dataObject->addData([
                'content' => $resultPage->getLayout()->getBlock('mageplaza.blog.category.edit')->getFormHtml()
                    . $resultPage->getLayout()->getBlock('mageplaza.blog.category.tree')
                        ->getBreadcrumbsJavascript($breadcrumbsPath, 'editingCategoryBreadcrumbs'),
                'messages' => $resultPage->getLayout()->getMessagesBlock()->getGroupedHtml(),
                'toolbar' => $resultPage->getLayout()->getBlock('page.actions.toolbar')->toHtml()
            ]);
            $this->_eventManager->dispatch(
                'mageplaza_blog_category_prepare_ajax_response',
                ['response' => $eventResponse, 'controller' => $this]
            );
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultJsonFactory->create();
            $resultJson->setHeader('Content-type', 'application/json', true);
            $resultJson->setData($eventResponse->getData());
            return $resultJson;
        }

        $resultPage->setActiveMenu('Mageplaza_Blog::category');
        $resultPage->getConfig()->getTitle()->prepend(__('Categories'));
        $resultPage->getConfig()->getTitle()->prepend($categoryId ? $category->getName() : __('Categories'));
        $resultPage->addBreadcrumb(__('Manage Categories'), __('Manage Categories'));
        return $resultPage;
    }
}
