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

namespace Mageplaza\Blog\Test\Unit\Controller\Adminhtml\Idea;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\AiIntegration\Exception\AiException;
use Mageplaza\Blog\Controller\Adminhtml\Idea\Generate;
use Mageplaza\Blog\Model\Ai\Ideas;
use Mageplaza\Blog\Model\Ai\Service;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GenerateTest extends TestCase
{
    /**
     * @var Http|MockObject
     */
    private $request;

    /**
     * @var Service|MockObject
     */
    private $service;

    /**
     * @var Ideas|MockObject
     */
    private $ideas;

    /**
     * @var array
     */
    private $response;

    /**
     * @var Generate
     */
    private $controller;

    protected function setUp(): void
    {
        $this->request = $this->createMock(Http::class);
        $this->request->method('isPost')->willReturn(true);
        $formKey = $this->createMock(Validator::class);
        $formKey->method('validate')->willReturn(true);
        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->method('isAllowed')->with('Mageplaza_Blog::ai_ideas')->willReturn(true);
        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($this->request);
        $context->method('getFormKeyValidator')->willReturn($formKey);
        $context->method('getAuthorization')->willReturn($auth);

        $json = $this->createMock(Json::class);
        $json->method('setData')->willReturnCallback(function ($data) use ($json) {
            $this->response = $data;

            return $json;
        });
        $factory = $this->createMock(JsonFactory::class);
        $factory->method('create')->willReturn($json);

        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(3);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $storeManager->method('getDefaultStoreView')->willReturn($store);

        $this->service = $this->createMock(Service::class);
        $this->service->method('isAvailable')->willReturn(true);
        $this->ideas = $this->createMock(Ideas::class);
        $this->controller = new Generate($context, $factory, $this->service, $this->ideas, $storeManager);
    }

    /**
     * @param array $params
     */
    private function params(array $params): void
    {
        $this->request->method('getParam')->willReturnCallback(
            fn($key, $default = null) => $params[$key] ?? $default
        );
    }

    public function testFreeIgnoresForgedStoreOffsetAndBatch(): void
    {
        $this->service->method('getLimits')->willReturn(
            ['max_topics' => 10, 'store_filter' => false, 'write_button' => false]
        );
        $this->params(['store_id' => 9, 'offset' => 300, 'batch' => 99, 'max_topics' => 500]);
        $this->ideas->expects($this->once())->method('generate')->with(3, 10, 0, false)
            ->willReturn(['topics' => [], 'removed' => 0, 'cached' => false]);

        $this->controller->execute();

        $this->assertTrue($this->response['success']);
    }

    public function testProLimitsAcceptStoreAndOffset(): void
    {
        $this->service->method('getLimits')->willReturn(
            ['max_topics' => 0, 'store_filter' => true, 'write_button' => true]
        );
        $this->params(['store_id' => 9, 'offset' => 150, 'refresh' => 1]);
        $this->ideas->expects($this->once())->method('generate')->with(9, 0, 150, true)
            ->willReturn(['topics' => [], 'removed' => 0, 'cached' => false]);

        $this->controller->execute();
    }

    public function testAiExceptionMapped(): void
    {
        $this->service->method('getLimits')->willReturn([]);
        $this->params([]);
        $this->ideas->method('generate')->willThrowException(AiException::create(AiException::QUOTA));
        $this->service->method('error')->willReturn(['success' => false, 'type' => 'quota', 'message' => 'm']);

        $this->controller->execute();

        $this->assertSame('quota', $this->response['type']);
        $this->assertFalse($this->response['success']);
    }
}
