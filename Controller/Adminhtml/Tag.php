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

abstract class Tag extends \Magento\Backend\App\Action
{
    /**
     * Tag Factory
     *
     * @var \Mageplaza\Blog\Model\TagFactory
     */
	public $tagFactory;

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
     * @param \Mageplaza\Blog\Model\TagFactory $tagFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mageplaza\Blog\Model\TagFactory $tagFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\App\Action\Context $context
    ) {
    
        $this->tagFactory            = $tagFactory;
        $this->coreRegistry          = $coreRegistry;
        $this->resultRedirectFactory = $context->getRedirect();
        parent::__construct($context);
    }

    /**
     * Init Tag
     *
     * @return \Mageplaza\Blog\Model\Tag
     */
	public function initTag()
    {
        $tagId  = (int) $this->getRequest()->getParam('tag_id');
        /** @var \Mageplaza\Blog\Model\Tag $tag */
        $tag    = $this->tagFactory->create();
        if ($tagId) {
            $tag->load($tagId);
        }
        $this->coreRegistry->register('mageplaza_blog_tag', $tag);
        return $tag;
    }
}
