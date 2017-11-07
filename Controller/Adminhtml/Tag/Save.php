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

namespace Mageplaza\Blog\Controller\Adminhtml\Tag;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\Blog\Controller\Adminhtml\Tag;
use Mageplaza\Blog\Model\TagFactory;

/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\Tag
 */
class Save extends Tag
{
    /**
     * @var \Magento\Backend\Helper\Js
     */
    public $jsHelper;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Mageplaza\Blog\Model\TagFactory $tagFactory
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Js $jsHelper,
        TagFactory $tagFactory
    )
    {
        $this->jsHelper = $jsHelper;

        parent::__construct($context, $registry, $tagFactory);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPost('tag')) {
            /** @var \Mageplaza\Blog\Model\Tag $tag */
            $tag = $this->initTag();

            $tag->addData($data);
            if ($posts = $this->getRequest()->getPost('posts', false)) {
                $tag->setPostsData($this->jsHelper->decodeGridSerializedInput($posts));
            }

            $this->_eventManager->dispatch('mageplaza_blog_tag_prepare_save', ['tag' => $tag, 'request' => $this->getRequest()]);

            try {
                $tag->save();

                $this->messageManager->addSuccess(__('The Tag has been saved.'));
                $this->_session->setData('mageplaza_blog_tag_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $tag->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('mageplaza_blog/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Tag.'));
            }
            $this->_getSession()->setData('mageplaza_blog_tag_data', $data);

            $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $tag->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('mageplaza_blog/*/');

        return $resultRedirect;
    }
}
