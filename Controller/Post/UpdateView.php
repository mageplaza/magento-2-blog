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
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Mageplaza\Blog\Model\TrafficFactory;

/**
 * Class UpdateView
 *
 * Increments a post's view counter from an AJAX call. Kept separate from the
 * post view page so that page can be served from Full Page Cache (the view
 * controller only runs on a cache miss, which would under-count views).
 *
 * @package Mageplaza\Blog\Controller\Post
 */
class UpdateView extends Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var TrafficFactory
     */
    protected $trafficFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * UpdateView constructor.
     *
     * @param Context $context
     * @param TrafficFactory $trafficFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        TrafficFactory $trafficFactory,
        JsonFactory $resultJsonFactory
    ) {
        $this->trafficFactory    = $trafficFactory;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute()
    {
        /** @var Json $result */
        $result = $this->resultJsonFactory->create();
        $postId = (int) $this->getRequest()->getParam('post_id');

        if (!$postId) {
            return $result->setData(['success' => false]);
        }

        try {
            $trafficModel = $this->trafficFactory->create()->load($postId, 'post_id');
            if ($trafficModel->getId()) {
                $trafficModel->setNumbersView($trafficModel->getNumbersView() + 1);
                $trafficModel->save();
            } else {
                $this->trafficFactory->create()
                    ->addData(['post_id' => $postId, 'numbers_view' => 1])
                    ->save();
            }
        } catch (Exception $e) {
            return $result->setData(['success' => false]);
        }

        return $result->setData(['success' => true]);
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * Skip form key validation: this endpoint only bumps a non-sensitive view
     * counter and is requested from cached pages where the form key may be stale.
     *
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
