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

namespace Mageplaza\Blog\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Blog\Controller\Adminhtml\Tag;
use Mageplaza\Blog\Model\TagFactory;

/**
 * Class Edit
 * @package Mageplaza\Blog\Controller\Adminhtml\Tag
 */
class Edit extends Tag
{
    /**
     * Page factory
     *
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * Edit constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param TagFactory $tagFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        TagFactory $tagFactory,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;

        parent::__construct($context, $registry, $tagFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page|Redirect|Page
     */
    public function execute()
    {
        /** @var \Mageplaza\Blog\Model\Tag $tag */
        $tag = $this->initTag();
        if (!$tag) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('*');

            return $resultRedirect;
        }

        $data = $this->_session->getData('mageplaza_blog_tag_data', true);
        if (!empty($data)) {
            $tag->setData($data);
        }

        $this->coreRegistry->register('mageplaza_blog_tag', $tag);

        /** @var \Magento\Backend\Model\View\Result\Page|Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Mageplaza_Blog::tag');
        $resultPage->getConfig()->getTitle()->set(__('Tags'));

        $title = $tag->getId() ? $tag->getName() : __('New Tag');
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
