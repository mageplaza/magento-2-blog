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
     * Review constructor.
     *
     * @param Context $context
     * @param PostFactory $postFactory
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PostFactory $postFactory,
        Data $helperData
    ) {
        $this->_helperBlog          = $helperData;

        $this->postFactory          = $postFactory;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     * @throws Exception
     */
    public function execute()
    {
        $id     = $this->getRequest()->getParam('post_id');
        $action = $this->getRequest()->getParam('action');
        $author = $this->_helperBlog->getCurrentAuthor();
        $post   = $this->postFactory->create()->load($id);

        if (!$author || !$post) {
            if ($action === '1') {
                $this->messageManager->addErrorMessage(__('Can\'t Like Post.'));
            }else{
                $this->messageManager->addErrorMessage(__('Can\'t Dislike Post.'));
            }
            return $this->getResponse()->representJson(Data::jsonEncode([
                'status' => 0
            ]));
        }

        if ($action === '1') {
            $newSumLike = (int)$post->getSumLike() + 1;
            $post->setData('sum_like', $newSumLike);
        }else{
            $newSumDislike = (int)$post->getSumDislike() + 1;
            $post->setData('sum_dislike', $newSumDislike);
        }
            $post->save();

        try {
            if ($action === '1') {
                $this->messageManager->addSuccessMessage(__('The post has been Like.'));
            }else{
                $this->messageManager->addSuccessMessage(__('The post has been Dislike.'));
            }

            return $this->getResponse()->representJson(Data::jsonEncode([
                'status' => 1
            ]));
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            return $this->getResponse()->representJson(Data::jsonEncode([
                'status' => 0
            ]));
        }
    }
}
