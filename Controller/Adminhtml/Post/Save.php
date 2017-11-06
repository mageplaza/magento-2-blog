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

use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Mageplaza\Blog\Controller\Adminhtml\Post;
use Mageplaza\Blog\Helper\Image;
use Mageplaza\Blog\Model\PostFactory;

/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class Save extends Post
{
    /**
     * JS helper
     *
     * @var \Magento\Backend\Helper\Js
     */
    public $jsHelper;

    /**
     * @var \Mageplaza\Blog\Helper\Image
     */
    protected $imageHelper;

    /**
     * Save constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Mageplaza\Blog\Model\PostFactory $postFactory
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Mageplaza\Blog\Helper\Image $imageHelper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PostFactory $postFactory,
        Js $jsHelper,
        Image $imageHelper
    )
    {
        $this->jsHelper    = $jsHelper;
        $this->imageHelper = $imageHelper;

        parent::__construct($postFactory, $registry, $context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('post')) {
            /** @var \Mageplaza\Blog\Model\Post $post */
            $post = $this->initPost();
            $this->prepareData($post, $data);

            $this->_eventManager->dispatch('mageplaza_blog_post_prepare_save', ['post' => $post, 'request' => $this->getRequest()]);

            try {
                $post->save();

                $this->messageManager->addSuccess(__('The post has been saved.'));
                $this->_getSession()->setData('mageplaza_blog_post_data', false);

                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $post->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('mageplaza_blog/*/');
                }

                return $resultRedirect;
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Post.'));
            }

            $this->_getSession()->setData('mageplaza_blog_post_data', $data);

            $resultRedirect->setPath('mageplaza_blog/*/edit', ['id' => $post->getId(), '_current' => true]);

            return $resultRedirect;
        }

        $resultRedirect->setPath('mageplaza_blog/*/');

        return $resultRedirect;
    }

    /**
     * @param \Mageplaza\Blog\Model\Post $post
     * @param array $data
     * @return $this
     */
    protected function prepareData($post, $data = [])
    {
        $this->imageHelper->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_TYPE_POST, $post->getImage());

        //set specify field data
        $timezone               = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $data['publish_date']   = $timezone->convertConfigTimeToUtc(isset($data['publish_date']) ? $data['publish_date'] : null);
        $data['modifier_id']    = $this->_auth->getUser()->getId();
        $data['categories_ids'] = (isset($data['categories_ids']) && $data['categories_ids']) ? explode(',', $data['categories_ids']) : [];

        $post->addData($data);

        if ($tags = $this->getRequest()->getPost('tags', false)) {
            $post->setTagsData(
                $this->jsHelper->decodeGridSerializedInput($tags)
            );
        }

        if ($topics = $this->getRequest()->getPost('topics', false)) {
            $post->setTopicsData(
                $this->jsHelper->decodeGridSerializedInput($topics)
            );
        }

        if ($products = $this->getRequest()->getPost('products', false)) {
            $post->setProductsData(
                $this->jsHelper->decodeGridSerializedInput($products)
            );
        }

        return $this;
    }
}
