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
namespace Mageplaza\Blog\Controller\Adminhtml;

abstract class Post extends \Magento\Backend\App\Action
{
    /**
     * Post Factory
     *
     * @var \Mageplaza\Blog\Model\PostFactory
     */
	public $postFactory;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
	public $coreRegistry;

    /**
     * Result redirect factory
     *
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
	public $resultRedirectFactory;

    /**
     * constructor
     *
     * @param \Mageplaza\Blog\Model\PostFactory $postFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mageplaza\Blog\Model\PostFactory $postFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context
    ) {
    
        $this->postFactory           = $postFactory;
        $this->coreRegistry          = $coreRegistry;
        $this->resultRedirectFactory = $context->getRedirect();
        parent::__construct($context);
    }

    /**
     * Init Post
     *
     * @return \Mageplaza\Blog\Model\Post
     */
	public function initPost()
    {
        $postId  = (int) $this->getRequest()->getParam('post_id');
        /** @var \Mageplaza\Blog\Model\Post $post */
        $post    = $this->postFactory->create();
        if ($postId) {
            $post->load($postId);
        }
        $this->coreRegistry->register('mageplaza_blog_post', $post);
        return $post;
    }
}
