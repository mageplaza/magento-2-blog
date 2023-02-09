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

namespace Mageplaza\Blog\Controller\Adminhtml\History;

use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Helper\Js;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\Blog\Controller\Adminhtml\History;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Image;
use Mageplaza\Blog\Model\Post as PostModel;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\PostHistoryFactory;
use RuntimeException;

/**
 * Class Save
 * @package Mageplaza\Blog\Controller\Adminhtml\History
 */
class Save extends History
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
     * @var Data
     */
    protected $_helperData;

    /**
     * @var PostHistoryFactory
     */
    protected $_postHistory;

    /**
     * @var TimezoneInterface
     */
    protected $_timezone;

    /**
     * Save constructor.
     *
     * @param PostHistoryFactory $postHistoryFactory
     * @param PostFactory $postFactory
     * @param Registry $coreRegistry
     * @param DateTime $date
     * @param Js $jsHelper
     * @param Image $imageHelper
     * @param Data $helperData
     * @param TimezoneInterface $timezone
     * @param Context $context
     */
    public function __construct(
        PostHistoryFactory $postHistoryFactory,
        PostFactory $postFactory,
        Registry $coreRegistry,
        DateTime $date,
        Js $jsHelper,
        Image $imageHelper,
        Data $helperData,
        TimezoneInterface $timezone,
        Context $context
    ) {
        $this->jsHelper = $jsHelper;
        $this->_helperData = $helperData;
        $this->imageHelper = $imageHelper;
        $this->_timezone = $timezone;

        parent::__construct($postHistoryFactory, $postFactory, $coreRegistry, $date, $context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPost('post')) {
            /** @var PostModel $post */
            $history = $this->initPostHistory(false);
            $this->prepareData($history, $data);

            try {
                $history->save();
                $this->messageManager->addSuccessMessage(__('The post history has been saved.'));

                $resultRedirect->setPath(
                    'mageplaza_blog/post/edit',
                    ['id' => $history->getPostId(), '_current' => true]
                );

                return $resultRedirect;
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Post.'));
            }
        }

        $resultRedirect->setPath('mageplaza_blog/*/edit', [
            'id' => $this->getRequest()->getParam('id'),
            'post_id' => $this->getRequest()->getParam('post_id'),
            'history' => true,
            '_current' => true
        ]);

        return $resultRedirect;
    }

    /**
     * @param $post
     * @param array $data
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function prepareData($post, $data = [])
    {
        if (!$this->getRequest()->getParam('image')) {
            try {
                $this->imageHelper->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_TYPE_POST, $post->getImage());
            } catch (Exception $exception) {
                $data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
            }
        } else {
            $data['image'] = '';
        }

        /** Set specify field data */
        $data['publish_date'] = $this->_timezone->convertConfigTimeToUtc(isset($data['publish_date'])
            ? $data['publish_date'] : null);
        $data['modifier_id'] = $this->_auth->getUser()->getId();
        $data['categories_ids'] = (isset($data['categories_ids']) && $data['categories_ids']) ? explode(
            ',',
            $data['categories_ids'] ?? ''
        ) : [];
        $data['tags_ids'] = (isset($data['tags_ids']) && $data['tags_ids'])
            ? explode(',', $data['tags_ids'] ?? '') : [];
        $data['topics_ids'] = (isset($data['topics_ids']) && $data['topics_ids']) ? explode(
            ',',
            $data['topics_ids'] ?? ''
        ) : [];

        if ($post->getCreatedAt() === null) {
            $data['created_at'] = $this->date->date();
        }
        $data['updated_at'] = $this->date->date();

        $post->addData($data);

        if ($products = $this->getRequest()->getPost('products', false)) {
            $post->setProductsData(
                $this->jsHelper->decodeGridSerializedInput($products)
            );
        } else {
            $productData = [];
            foreach ($post->getProductsPosition() as $key => $value) {
                $productData[$key] = ['position' => $value];
            }
            $post->setProductsData($productData);
        }

        return $this;
    }
}
