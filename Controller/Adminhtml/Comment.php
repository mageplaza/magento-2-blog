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
use Mageplaza\Blog\Model\CommentFactory;

/**
 * Class Comment
 * @package Mageplaza\Blog\Controller\Adminhtml
 */
abstract class Comment extends Action
{
    /**
     * @var \Mageplaza\Blog\Model\ResourceModel\Comment
     */
    protected $commentResource;

    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_Blog::comment';

    /**
     * @var CommentFactory
     */
    public $commentFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * Comment constructor.
     *
     * @param CommentFactory $commentFactory
     * @param Registry $coreRegistry
     * @param Context $context
     * @param \Mageplaza\Blog\Model\ResourceModel\Comment $commentResource
     */
    public function __construct(
        CommentFactory $commentFactory,
        Registry $coreRegistry,
        Context $context,
        \Mageplaza\Blog\Model\ResourceModel\Comment $commentResource
    ) {
        $this->commentResource = $commentResource;
        $this->commentFactory = $commentFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @param bool $register
     *
     * @return bool|\Mageplaza\Blog\Model\Comment
     */
    protected function initComment($register = false)
    {
        $cmtId = $this->getRequest()->getParam("id");

        /** @var \Mageplaza\Blog\Model\Post $post */
        $comment = $this->commentFactory->create();

        if ($cmtId) {
            $this->commentResource->load($comment, $cmtId);
            if (!$comment->getId()) {
                $this->messageManager->addErrorMessage(__('This comment no longer exists.'));

                return false;
            }
        }

        if ($register) {
            $this->coreRegistry->register('mageplaza_blog_comment', $comment);
        }

        return $comment;
    }
}
