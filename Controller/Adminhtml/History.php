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

namespace Mageplaza\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\PostHistory;
use Mageplaza\Blog\Model\PostHistoryFactory;

/**
 * Class Post
 * @package Mageplaza\Blog\Controller\Adminhtml
 */
abstract class History extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_Blog::post';

    /**
     * Post History Factory
     *
     * @var PostHistoryFactory
     */
    public $postHistoryFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * Post constructor.
     *
     * @param PostHistoryFactory $postHistoryFactory
     * @param PostFactory $postFactory
     * @param Registry $coreRegistry
     * @param DateTime $date
     * @param Context $context
     */
    public function __construct(
        PostHistoryFactory $postHistoryFactory,
        PostFactory $postFactory,
        Registry $coreRegistry,
        DateTime $date,
        Context $context
    ) {
        $this->postHistoryFactory = $postHistoryFactory;
        $this->postFactory = $postFactory;
        $this->coreRegistry = $coreRegistry;
        $this->date = $date;

        parent::__construct($context);
    }

    /**
     * @param bool $register
     *
     * @return bool|PostHistory
     */
    protected function initPostHistory($register = false)
    {
        $historyId = (int)$this->getRequest()->getParam('id');

        /** @var \Mageplaza\Blog\Model\Post $post */
        $history = $this->postHistoryFactory->create();
        if ($historyId) {
            $history->load($historyId);
            if (!$history->getId()) {
                $this->messageManager->addErrorMessage(__('This History no longer exists.'));

                return false;
            }
        }

        if ($register) {
            $this->coreRegistry->register('mageplaza_blog_post_history', $history);
        }

        return $history;
    }
}
