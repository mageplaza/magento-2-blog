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

namespace Mageplaza\Blog\Test\Unit\Model\Ai;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Mageplaza\AiIntegration\Api\AiClientInterface;
use Mageplaza\AiIntegration\Api\ConnectionStatusInterface;
use Mageplaza\AiIntegration\Api\Data\ResultInterface;
use Mageplaza\AiIntegration\Api\ModelListInterface;
use Mageplaza\AiIntegration\Helper\Data as AiHelper;
use Mageplaza\AiIntegration\Exception\AiException;
use Mageplaza\Blog\Model\Ai\Service;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServiceTest extends TestCase
{
    /**
     * @var AiClientInterface|MockObject
     */
    private $client;

    /**
     * @var ConnectionStatusInterface|MockObject
     */
    private $status;

    /**
     * @var ModelListInterface|MockObject
     */
    private $modelList;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $config;

    /**
     * @var Service
     */
    private $service;

    protected function setUp(): void
    {
        $this->client = $this->createMock(AiClientInterface::class);
        $this->status = $this->createMock(ConnectionStatusInterface::class);
        $this->modelList = $this->createMock(ModelListInterface::class);
        $this->config = $this->createMock(ScopeConfigInterface::class);
        $this->service = new Service(
            $this->client,
            $this->status,
            $this->modelList,
            $this->config,
            $this->createMock(AiHelper::class),
            ['assist' => ['max' => 3]]
        );
    }

    public function testAvailabilityAndLimits(): void
    {
        $this->status->method('isAvailable')->willReturn(true);
        $this->assertTrue($this->service->isAvailable());
        $this->assertSame(['max' => 3], $this->service->getLimits('assist'));
        $this->assertSame([], $this->service->getLimits('none'));
    }

    public function testUnavailable(): void
    {
        $this->status->method('isAvailable')->willReturn(false);
        $this->assertFalse($this->service->isAvailable());
    }

    public function testRunWithSchemaReturnsDataAndSetsOptions(): void
    {
        $this->config->method('getValue')->willReturn(' gpt-x ');
        $this->modelList->method('isCustomModel')->willReturn(false);
        $result = $this->createMock(ResultInterface::class);
        $result->method('getData')->willReturn(['a' => 1]);
        $this->client->expects($this->once())->method('complete')->with(
            [['role' => 'user', 'content' => 'hi']],
            ['type' => 'object'],
            ['max_tokens' => 10, 'model' => 'gpt-x', 'source' => 'blog', 'max_retries' => 0]
        )->willReturn($result);

        $this->assertSame(
            ['a' => 1],
            $this->service->run([['role' => 'user', 'content' => 'hi']], ['type' => 'object'], ['max_tokens' => 10])
        );
    }

    public function testRunWithoutSchemaReturnsText(): void
    {
        $this->config->method('getValue')->willReturn('');
        $result = $this->createMock(ResultInterface::class);
        $result->method('getText')->willReturn('plain');
        $this->client->expects($this->once())->method('complete')->with(
            $this->anything(),
            null,
            ['source' => 'blog', 'max_retries' => 0]
        )->willReturn($result);

        $this->assertSame('plain', $this->service->run([]));
    }

    public function testCustomModelDropsModelOption(): void
    {
        $this->config->method('getValue')->willReturn('gpt-x');
        $this->modelList->method('isCustomModel')->willReturn(true);
        $result = $this->createMock(ResultInterface::class);
        $result->method('getText')->willReturn('');
        $this->client->expects($this->once())->method('complete')->with(
            $this->anything(),
            null,
            ['source' => 'blog', 'max_retries' => 0]
        )->willReturn($result);

        $this->service->run([], null, ['model' => 'forced']);
    }

    public function testNullDataWithSchemaIsInvalidResponse(): void
    {
        $this->config->method('getValue')->willReturn('');
        $result = $this->createMock(ResultInterface::class);
        $result->method('getData')->willReturn(null);
        $this->client->method('complete')->willReturn($result);

        $this->expectException(AiException::class);
        $this->service->run([], ['type' => 'object']);
    }

    /**
     * @param string $type
     * @dataProvider errorProvider
     */
    #[DataProvider('errorProvider')]
    public function testErrorMapsAiException(string $type): void
    {
        $e = AiException::create($type);
        $this->assertSame(
            ['success' => false, 'type' => $e->getType(), 'message' => $e->getMessage()],
            $this->service->error($e)
        );
    }

    /**
     * @return array
     */
    public static function errorProvider(): array
    {
        return [
            'quota' => [AiException::QUOTA],
            'truncated' => [AiException::TRUNCATED],
            'timeout' => [AiException::TIMEOUT],
            'invalid key' => [AiException::INVALID_KEY],
        ];
    }
}
