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
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\ResourceModel\Author\CollectionFactory;

/**
 * Class MassDelete
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class MassDelete extends Action
{
    /**
     * Mass Action Filter
     *
     * @var Filter
     */
    public $filter;

    /**
     * Collection Factory
     *
     * @var CollectionFactory
     */
    public $collectionFactory;

    /**
     * @var PostFactory
     */
    protected $_postFactory;

    /**
     * constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param PostFactory $postFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        PostFactory $postFactory
    ) {
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->_postFactory      = $postFactory;

        parent::__construct($context);
    }

    /**
     * @return $this|ResponseInterface|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());

        if ($this->allowMassDeleteAuthor()) {
            try {
                $collection->walk('delete');

                $this->messageManager->addSuccessMessage(__('Authors has been deleted.'));
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Something wrong when delete Authors.'));
            }
        } else {
            $this->messageManager->addErrorMessage('One of the authors has post. You can not delete this one.');
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return bool
     * @throws LocalizedException
     */
    public function allowMassDeleteAuthor()
    {
        $post           = $this->_postFactory->create();
        $postCollection = $post->getCollection();
        $collection     = $this->filter->getCollection($this->collectionFactory->create());

        foreach ($collection as $item) {
            if ($postCollection->addFieldToFilter('author_id', ['eq' => $item->getId()])->getSize() > 0) {
                return false;
            }
        }

        return true;
    }
}
