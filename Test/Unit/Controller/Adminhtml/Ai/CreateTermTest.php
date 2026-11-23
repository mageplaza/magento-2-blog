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
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Mageplaza\Blog\Controller\Adminhtml\Ai\CreateTerm;
use Mageplaza\Blog\Model\Ai\Service;
use Mageplaza\Blog\Model\Ai\TermCreator;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class CreateTermTest extends TestCase
{
    public function testWorksWhenAiDisabled(): void
    {
        $request = $this->createMock(\Magento\Framework\App\Request\Http::class);
        $request->method('isPost')->willReturn(true);
        $request->method('getParam')->willReturnCallback(
            fn($key) => ['type' => 'tag', 'name' => 'x'][$key] ?? null
        );
        $formKey = $this->createMock(Validator::class);
        $formKey->method('validate')->willReturn(true);
        $auth = $this->createMock(AuthorizationInterface::class);
        $auth->method('isAllowed')->willReturn(true);
        $context = $this->createMock(Context::class);
        $context->method('getRequest')->willReturn($request);
        $context->method('getFormKeyValidator')->willReturn($formKey);
        $context->method('getAuthorization')->willReturn($auth);
        $response = null;
        $json = $this->createMock(Json::class);
        $json->method('setData')->willReturnCallback(function ($data) use ($json, &$response) {
            $response = $data;

            return $json;
        });
        $factory = $this->createMock(JsonFactory::class);
        $factory->method('create')->willReturn($json);
        $service = $this->createMock(Service::class);
        $service->method('isAvailable')->willReturn(false);
        $creator = $this->createMock(TermCreator::class);
        $creator->expects($this->once())->method('createOrFind')->willReturn(['id' => 1, 'name' => 'x']);

        (new CreateTerm($context, $factory, $service, $creator, $this->createMock(LoggerInterface::class)))->execute();

        $this->assertTrue($response['success']);
    }
}
