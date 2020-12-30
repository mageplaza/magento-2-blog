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
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\Blog\Controller\Adminhtml\Author;
use Mageplaza\Blog\Helper\Image;
use Mageplaza\Blog\Model\AuthorFactory;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\Author
 */
class Save extends Author
{
    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param AuthorFactory $authorFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Image $imageHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AuthorFactory $authorFactory,
        CustomerRepositoryInterface $customerRepository,
        Image $imageHelper
    ) {
        $this->imageHelper        = $imageHelper;
        $this->customerRepository = $customerRepository;

        parent::__construct($context, $registry, $authorFactory);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('author')) {
            /** @var \Mageplaza\Blog\Model\Author $author */
            $author = $this->initAuthor();
            $this->prepareData($author, $data);

            $this->_eventManager->dispatch(
                'mageplaza_blog_author_prepare_save',
                ['author' => $author, 'request' => $this->getRequest()]
            );

            try {
                $author->save();

                $this->messageManager->addSuccessMessage(__('The Author has been saved.'));
                $this->_getSession()->setData('mageplaza_blog_author_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $author->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('mageplaza_blog/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Author.'));
            }

            $this->_getSession()->setData('mageplaza_blog_author_data', $data);

            $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $author->getId(), '_current' => true]);

            return $resultRedirect;
        }
        $resultRedirect->setPath('mageplaza_blog/*/');

        return $resultRedirect;
    }

    /**
     * @param $author
     * @param $data
     *
     * @return $this
     */
    public function prepareData($author, $data)
    {
        // upload image
        if (!$this->getRequest()->getParam('image')) {
            try {
                $this->imageHelper->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_TYPE_AUTH, $author->getImage());
            } catch (Exception $exception) {
                $data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
            }
        }
        if ($this->getRequest()->getParam('image') && isset($this->getRequest()->getParam('image')['delete'])) {
            $data['image'] = '';
        }
        // set data
        if (!empty($data)) {
            if (!empty($data['customer_id'])) {
                try {
                    $data['email'] = $this->customerRepository->getById($data['customer_id'])->getEmail();
                } catch (Exception $e) {
                    $data['email'] = '';
                }
            }

            $author->addData($data);
        }

        return $this;
    }
}
