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

namespace Mageplaza\Blog\Controller\Adminhtml\Author;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\Blog\Controller\Adminhtml\Author;
use Mageplaza\Blog\Helper\Image;
use Mageplaza\Blog\Model\AuthorFactory;

/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\Author
 */
class Save extends Author
{
    /**
     * @var \Mageplaza\Blog\Helper\Image
     */
    protected $imageHelper;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Mageplaza\Blog\Model\AuthorFactory $authorFactory
     * @param \Mageplaza\Blog\Helper\Image $imageHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AuthorFactory $authorFactory,
        Image $imageHelper
    )
    {
        $this->imageHelper = $imageHelper;

        parent::__construct($context, $registry, $authorFactory);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('author')) {
            /** @var \Mageplaza\Blog\Model\Author $author */
            $author = $this->initAuthor();

            $this->imageHelper->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_TYPE_AUTH, $author->getImage());

            if (!empty($data)) {
                $author->addData($data);
            }

            $this->_eventManager->dispatch('mageplaza_blog_author_prepare_save', ['author' => $author, 'request' => $this->getRequest()]);

            try {
                $author->save();

                $this->messageManager->addSuccess(__('The Author has been saved.'));
                $this->_getSession()->setData('mageplaza_blog_author_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('mageplaza_blog/*/edit', ['_current' => true]);
                } else {
                    $resultRedirect->setPath('mageplaza_blog/*/');
                }

                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Author.'));
            }

            $this->_getSession()->setData('mageplaza_blog_author_data', $data);

            $resultRedirect->setPath('mageplaza_blog/*/edit', ['_current' => true]);

            return $resultRedirect;
        }
        $resultRedirect->setPath('mageplaza_blog/*/');

        return $resultRedirect;
    }
}
