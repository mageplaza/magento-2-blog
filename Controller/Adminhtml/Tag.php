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

namespace Mageplaza\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\TagFactory;

/**
 * Class Tag
 * @package Mageplaza\Blog\Controller\Adminhtml
 */
abstract class Tag extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_Blog::tag';

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
     * Tag constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Mageplaza\Blog\Model\TagFactory $tagFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        TagFactory $tagFactory
    )
    {
        $this->tagFactory            = $tagFactory;
        $this->coreRegistry          = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @param bool $register
     * @return bool|\Mageplaza\Blog\Model\Tag
     */
    protected function initTag($register = false)
    {
        $tagId = (int)$this->getRequest()->getParam('id');

        /** @var \Mageplaza\Blog\Model\Tag $tag */
        $tag = $this->tagFactory->create();
        if ($tagId) {
            $tag->load($tagId);
            if (!$tag->getId()) {
                $this->messageManager->addErrorMessage(__('This tag no longer exists.'));

                return false;
            }
        }

        if($register){
            $this->coreRegistry->register('mageplaza_blog_tag', $tag);
        }

        return $tag;
    }
}
