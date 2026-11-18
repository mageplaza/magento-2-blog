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

declare(strict_types=1);

namespace Mageplaza\Blog\Controller\Adminhtml\Ai;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AiIntegration\Exception\AiException;
use Mageplaza\Blog\Model\Ai\Service;

abstract class AbstractAi extends Action implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Service
     */
    protected $service;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Service $service
     */
    public function __construct(Context $context, JsonFactory $resultJsonFactory, Service $service)
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->service = $service;

        parent::__construct($context);
    }

    /**
     * @param RequestInterface $request
     * @return array
     * @throws LocalizedException
     * @throws AiException
     */
    abstract protected function process(RequestInterface $request): array;

    /**
     * @return Json
     */
    public function execute()
    {
        return $this->resultJsonFactory->create()->setData($this->handle());
    }

    /**
     * @return array
     */
    private function handle(): array
    {
        $request = $this->getRequest();
        if (!$request->isPost() || !$this->_formKeyValidator->validate($request)) {
            return ['success' => false, 'message' => (string)__('Invalid request.')];
        }
        if (!$this->_authorization->isAllowed(static::ADMIN_RESOURCE)) {
            return ['success' => false, 'message' => (string)__('You are not allowed to use this feature.')];
        }
        if (!$this->service->isAvailable()) {
            return ['success' => false, 'message' => (string)__('Configure AI to use this feature')];
        }
        try {
            return ['success' => true] + $this->process($request);
        } catch (AiException $e) {
            return $this->service->error($e);
        } catch (LocalizedException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
