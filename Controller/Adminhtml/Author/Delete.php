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

namespace Mageplaza\Blog\Controller\Adminhtml\Author;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Mageplaza\Blog\Controller\Adminhtml\Author;
use Mageplaza\Blog\Model\AuthorFactory;
use Mageplaza\Blog\Model\PostFactory;

/**
 * Class Delete
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class Delete extends Author
{
    /**
     * @var PostFactory
     */
    protected $_postFactory;

    /**
     * Delete constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param AuthorFactory $authorFactory
     * @param PostFactory $postFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AuthorFactory $authorFactory,
        PostFactory $postFactory
    ) {
        $this->_postFactory = $postFactory;

        parent::__construct($context, $coreRegistry, $authorFactory);
    }

    /**
     * @return Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id = $this->getRequest()->getParam('id')) {
            $post = $this->_postFactory->create();
            $postCollectionSize = $post->getCollection()->addFieldToFilter('author_id', ['eq' => $id])->getSize();
            if ($postCollectionSize > 0) {
                $this->messageManager->addErrorMessage(__('You can not delete this author.'
                    . ' This is the author of %1 post(s)', $postCollectionSize));
                $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $id]);

                return $resultRedirect;
            }
            try {
                $this->authorFactory->create()
                    ->load($id)
                    ->delete();

                $this->messageManager->addSuccessMessage(__('The Author has been deleted.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $id]);

                return $resultRedirect;
            }
        } else {
            $this->messageManager->addErrorMessage(__('Author to delete was not found.'));
        }

        $resultRedirect->setPath('mageplaza_blog/*/');

        return $resultRedirect;
    }
}
