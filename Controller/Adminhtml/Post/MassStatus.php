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

namespace Mageplaza\Blog\Controller\Adminhtml\Post;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory;

/**
 * Class MassStatus
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class MassStatus extends Action
{
    /**
     * Mass Action Filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    public $filter;

    /**
     * Collection Factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    public $collectionFactory;

    /**
     * constructor
     *
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $collectionFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    )
    {
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $status     = (int)$this->getRequest()->getParam('status');

        $postUpdated = 0;
        foreach ($collection as $post) {
            try {
                $post->setEnabled($status)
                    ->save();

                $postUpdated++;
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->_getSession()->addException($e, __('Something went wrong while updating status for %1.', $post->getName()));
            }
        }

        if ($postUpdated) {
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been updated.', $postUpdated));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
