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

namespace Mageplaza\Blog\Observer;

use Exception;
use Magento\Customer\Controller\Account\CreatePost;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\AuthorFactory;

/**
 * Class CreateAuthor
 * @package Mageplaza\Blog\Observer
 */
class CreateAuthor implements ObserverInterface
{

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * @var AuthorFactory
     */
    protected $author;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @param Data $helper
     * @param AuthorFactory $authorFactory
     * @param ManagerInterface $manager
     */
    public function __construct(
        Data $helper,
        AuthorFactory $authorFactory,
        ManagerInterface $manager
    ) {
        $this->author = $authorFactory;
        $this->_helper = $helper;
        $this->messageManager = $manager;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $accountController = $observer->getData('account_controller');
        $customer = $observer->getData('customer');

        /** @var CreatePost $accountController */
        if ($this->_helper->isEnabled() && $accountController->getRequest()->getParam('is_mp_author')) {
            $data = [
                'customer_id' => $customer->getId(),
                'name' => $customer->getFirstname(),
                'type' => '1',
                'status' => $this->_helper->getConfigGeneral('auto_approve') ? 1 : 0
            ];
            $author = $this->author->create();
            $author->addData($data);
            try {
                $author->save();
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Author.'));
            }
        }
    }
}
