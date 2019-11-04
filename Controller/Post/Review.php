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

namespace Mageplaza\Blog\Controller\Post;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\PostLikeFactory;
use Mageplaza\Blog\Model\ResourceModel\PostLike\Collection;

/**
 * Class Review
 * @package Mageplaza\Blog\Controller\Post
 */
class Review extends Action
{

    /**
     * @var Data
     */
    protected $_helperBlog;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var PostLike
     */
    protected $_postLike;

    /**
     * @var Collection
     */
    protected $_postLikeCollection;

    /**
     * Review constructor.
     *
     * @param Context $context
     * @param PostFactory $postFactory
     * @param Collection $postLikeCollection
     * @param PostLikeFactory $postLikeFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PostFactory $postFactory,
        Collection $postLikeCollection,
        PostLikeFactory $postLikeFactory,
        Data $helperData
    ) {
        $this->_helperBlog         = $helperData;
        $this->_postLikeCollection = $postLikeCollection;
        $this->_postLike           = $postLikeFactory;
        $this->postFactory         = $postFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $id         = $this->getRequest()->getParam('post_id');
        $action     = $this->getRequest()->getParam('action');
        $customerId = $this->_helperBlog->getCurrentUser();
        $post       = $this->postFactory->create()->load($id);
        $sum = $this->_postLike->create()->getCollection()->addFieldToFilter('action', $action)
            ->addFieldToFilter('post_id', $id);
        $like       = $this->_postLikeCollection->addFieldToFilter('entity_id', $customerId)
            ->addFieldToFilter('post_id', $post->getId());

        if (!$customerId || !$post || $like->count() > 0) {
            if ($action === '1') {
                $this->messageManager->addErrorMessage(__('Can\'t Like Post.'));
            } else {
                $this->messageManager->addErrorMessage(__('Can\'t Dislike Post.'));
            }

            return $this->getResponse()->representJson(Data::jsonEncode([
                'status' => 0,
                'type'   => $action
            ]));
        }

        try {
            $this->_postLike->create()->setData(
                [
                    'post_id' => $post->getId(),
                    'action' => $action,
                    'entity_id' => $customerId
                ]
            )->save();

            if ($action === '1') {
                $this->messageManager->addSuccessMessage(__('The post has been Like.'));
            } else {
                $this->messageManager->addSuccessMessage(__('The post has been Dislike.'));
            }

            return $this->getResponse()->representJson(Data::jsonEncode([
                'status' => 1,
                'type'   => $action,
                'sum'    => $sum->count()
            ]));
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            return $this->getResponse()->representJson(Data::jsonEncode([
                'status' => 0,
                'type'   => $action,
                'sum'    => $sum->count()
            ]));
        }
    }
}
