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
use Mageplaza\Blog\Model\PostFactory;

/**
 * Class Post
 * @package Mageplaza\Blog\Controller\Adminhtml
 */
abstract class Post extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_Blog::post';

    /**
     * Post Factory
     *
     * @var PostFactory
     */
    public $postFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * Post constructor.
     *
     * @param PostFactory $postFactory
     * @param Registry $coreRegistry
     * @param Context $context
     */
    public function __construct(
        PostFactory $postFactory,
        Registry $coreRegistry,
        Context $context
    ) {
        $this->postFactory = $postFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @param bool $register
     * @param bool $isSave
     *
     * @return bool|\Mageplaza\Blog\Model\Post
     */
    protected function initPost($register = false, $isSave = false)
    {
        $postId = (int)$this->getRequest()->getParam('id');
        $duplicate = $this->getRequest()->getParam('post')['duplicate'] ?? null;

        /** @var \Mageplaza\Blog\Model\Post $post */
        $post = $this->postFactory->create();
        if ($postId) {
            if (!$isSave || !$duplicate) {
                $post->load($postId);
                if (!$post->getId()) {
                    $this->messageManager->addErrorMessage(__('This post no longer exists.'));

                    return false;
                }
            }
        }

        if (!$post->getAuthorId()) {
            $post->setAuthorId($this->_auth->getUser()->getId());
        }

        if ($register) {
            $this->coreRegistry->register('mageplaza_blog_post', $post);
        }

        return $post;
    }
}
