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

namespace Mageplaza\Blog\Controller\Adminhtml\Post;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Controller\Adminhtml\Post;
use Mageplaza\Blog\Helper\Image;
use Mageplaza\Blog\Model\PostFactory;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\Post
 */
class Save extends Post
{
    /**
     * JS helper
     *
     * @var Js
     */
    public $jsHelper;

    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * Save constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param PostFactory $postFactory
     * @param Js $jsHelper
     * @param Image $imageHelper
     * @param DateTime $date
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PostFactory $postFactory,
        Js $jsHelper,
        Image $imageHelper,
        DateTime $date
    ) {
        $this->jsHelper = $jsHelper;
        $this->imageHelper = $imageHelper;
        $this->date = $date;

        parent::__construct($postFactory, $registry, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws FileSystemException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('post')) {
            /** @var \Mageplaza\Blog\Model\Post $post */
            $post = $this->initPost(false, true);
            $this->prepareData($post, $data);

            $this->_eventManager->dispatch(
                'mageplaza_blog_post_prepare_save',
                ['post' => $post, 'request' => $this->getRequest()]
            );

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
            } catch (RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (Exception $e) {
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
     * @param $post
     * @param array $data
     *
     * @return $this
     * @throws FileSystemException
     */
    protected function prepareData($post, $data = [])
    {
        try {
            $this->imageHelper->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_TYPE_POST, $post->getImage());
        } catch (Exception $exception) {
            $data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
        }

        /** Set specify field data */
        $timezone = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $data['publish_date'] .= ' ' . $data['publish_time'][0]
                                 . ':' . $data['publish_time'][1] . ':' . $data['publish_time'][2];
        $data['publish_date'] = $timezone->convertConfigTimeToUtc(isset($data['publish_date'])
            ? $data['publish_date'] : null);
        $data['modifier_id'] = $this->_auth->getUser()->getId();
        $data['categories_ids'] = (isset($data['categories_ids']) && $data['categories_ids']) ? explode(
            ',',
            $data['categories_ids']
        ) : [];
        $data['tags_ids'] = (isset($data['tags_ids']) && $data['tags_ids'])
            ? explode(',', $data['tags_ids']) : [];
        $data['topics_ids'] = (isset($data['topics_ids']) && $data['topics_ids']) ? explode(
            ',',
            $data['topics_ids']
        ) : [];

        if ($post->getCreatedAt() == null) {
            $data['created_at'] = $this->date->date();
        }
        $data['updated_at'] = $this->date->date();

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
        } else {
            $prodcutData = [];
            foreach ($post->getProductsPosition() as $key => $value) {
                $prodcutData[$key] = ['position' => $value];
            }
            $post->setProductsData($prodcutData);
        }

        return $this;
    }
}
