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

namespace Mageplaza\Blog\Test\Unit\Controller\Adminhtml\Ai;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Mageplaza\AiIntegration\Exception\AiException;
use Mageplaza\Blog\Controller\Adminhtml\Ai\Assist;
use Mageplaza\Blog\Model\Ai\Assist as AssistModel;
use Mageplaza\Blog\Model\Ai\PostStore;
use Mageplaza\Blog\Model\Ai\Service;
use Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory as TopicCollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssistTest extends TestCase
{
    /**
     * @var RequestInterface|MockObject
     */
    private $request;

    /**
     * @var Validator|MockObject
     */
    private $formKey;

    /**
     * @var AuthorizationInterface|MockObject
     */
    private $auth;

    /**
     * @var Service|MockObject
     */
    private $service;

    /**
     * @var AssistModel|MockObject
     */
    private $assist;

    /**
     * @var PostStore|MockObject
     */
    private $postStore;

    /**
     * @var array
     */
    private $response;

    /**
     * @var \Psr\Log\LoggerInterface|MockObject
     */
    private $logger;

    /**
     * @var Assist
     */
    private $controller;

    protected function setUp(): void
    {
        $this->request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $this->formKey = $this->createMock(Validator::class);
        $this->auth = $this->createMock(AuthorizationInterface::class);
        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($this->request);
        $context->method('getFormKeyValidator')->willReturn($this->formKey);
        $context->method('getAuthorization')->willReturn($this->auth);

        $json = $this->createMock(Json::class);
        $json->method('setData')->willReturnCallback(function ($data) use ($json) {
            $this->response = $data;

            return $json;
        });
        $factory = $this->createMock(JsonFactory::class);
        $factory->method('create')->willReturn($json);

        $this->service = $this->createMock(Service::class);
        $this->assist = $this->createMock(AssistModel::class);
        $this->postStore = $this->createMock(PostStore::class);
        $this->postStore->method('getStoreId')->willReturn(1);
        $this->logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $this->controller = new Assist(
            $context,
            $factory,
            $this->service,
            $this->assist,
            $this->postStore,
            $this->createMock(TagCollectionFactory::class),
            $this->createMock(TopicCollectionFactory::class),
            $this->logger
        );
    }

    private function allow(bool $post = true, bool $key = true, bool $acl = true, bool $ai = true): void
    {
        $this->request->method('isPost')->willReturn($post);
        $this->formKey->method('validate')->willReturn($key);
        $this->auth->method('isAllowed')->with('Mageplaza_Blog::ai_assist')->willReturn($acl);
        $this->service->method('isAvailable')->willReturn($ai);
    }

    public function testRejectsGetAndBadFormKey(): void
    {
        $this->allow(false, true);
        $this->assist->expects($this->never())->method('generate');
        $this->controller->execute();
        $this->assertFalse($this->response['success']);
    }

    public function testRejectsBadFormKey(): void
    {
        $this->allow(true, false);
        $this->assist->expects($this->never())->method('generate');
        $this->controller->execute();
        $this->assertFalse($this->response['success']);
    }

    public function testRejectsWithoutAcl(): void
    {
        $this->allow(true, true, false);
        $this->assist->expects($this->never())->method('generate');
        $this->controller->execute();
        $this->assertFalse($this->response['success']);
    }

    public function testRejectsWhenAiUnavailable(): void
    {
        $this->allow(true, true, true, false);
        $this->assist->expects($this->never())->method('generate');
        $this->controller->execute();
        $this->assertSame('Configure AI to use this feature', $this->response['message']);
    }

    public function testSuccess(): void
    {
        $this->allow();
        $this->request->method('getParam')->willReturnCallback(
            fn($key, $default = null) => ['type' => 'titles', 'name' => 'N', 'content' => 'C'][$key] ?? $default
        );
        $this->assist->expects($this->once())->method('generate')
            ->with('titles', 'N', 'C', [], [], 1)->willReturn(['titles' => ['A']]);
        $this->controller->execute();
        $this->assertSame(['success' => true, 'titles' => ['A']], $this->response);
    }

    public function testInvalidTypeError(): void
    {
        $this->allow();
        $this->request->method('getParam')->willReturn('bad');
        $this->assist->method('generate')
            ->willThrowException(new \Magento\Framework\Exception\LocalizedException(__('Invalid request.')));
        $this->controller->execute();
        $this->assertSame(['success' => false, 'message' => 'Invalid request.'], $this->response);
    }

    public function testThrowableIsLoggedAndGeneric(): void
    {
        $this->allow();
        $this->assist->method('generate')->willThrowException(new \RuntimeException('boom'));
        $this->logger->expects($this->once())->method('critical');
        $this->controller->execute();
        $this->assertFalse($this->response['success']);
        $this->assertStringNotContainsString('boom', $this->response['message']);
    }

    public function testAiExceptionMapped(): void
    {
        $this->allow();
        $e = AiException::create(AiException::QUOTA);
        $this->assist->method('generate')->willThrowException($e);
        $this->service->method('error')->willReturn(['success' => false, 'type' => 'quota', 'message' => 'm']);
        $this->controller->execute();
        $this->assertSame('quota', $this->response['type']);
    }
}
