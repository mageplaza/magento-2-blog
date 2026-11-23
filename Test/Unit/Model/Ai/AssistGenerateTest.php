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
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AiIntegration\Api\AiClientInterface;
use Mageplaza\AiIntegration\Api\ConnectionStatusInterface;
use Mageplaza\AiIntegration\Api\Data\ResultInterface;
use Mageplaza\AiIntegration\Api\ModelListInterface;
use Mageplaza\AiIntegration\Helper\Data as AiHelper;
use Mageplaza\AiIntegration\Exception\AiException;
use Mageplaza\Blog\Model\Ai\Assist;
use Mageplaza\Blog\Model\Ai\OutputFilter;
use Mageplaza\Blog\Model\Ai\Service;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AssistGenerateTest extends TestCase
{
    /**
     * @var AiClientInterface|MockObject
     */
    private $client;

    /**
     * @var Assist
     */
    private $assist;

    protected function setUp(): void
    {
        $this->client = $this->createMock(AiClientInterface::class);
        $config = $this->createMock(ScopeConfigInterface::class);
        $config->method('getValue')->willReturnCallback(
            fn($path) => $path === Service::XML_PATH_LANGUAGE ? 'Vietnamese' : ''
        );
        $service = new Service(
            $this->client,
            $this->createMock(ConnectionStatusInterface::class),
            $this->createMock(ModelListInterface::class),
            $config,
            $this->createMock(AiHelper::class)
        );
        $this->assist = new Assist(new OutputFilter(), $service);
    }

    /**
     * @param array $data
     */
    private function respond(array $data): void
    {
        $result = $this->createMock(ResultInterface::class);
        $result->method('getData')->willReturn($data);
        $this->client->method('complete')->willReturn($result);
    }

    public function testShortSendsPromptAndSchema(): void
    {
        $result = $this->createMock(ResultInterface::class);
        $result->method('getData')->willReturn(['short_description' => '<b>Hi</b> {{var x}}there']);
        $this->client->expects($this->once())->method('complete')->with(
            $this->callback(function (array $messages) {
                $text = $messages[0]['content'];

                return strpos($text, 'Output language: Vietnamese') !== false
                    && strpos($text, '{{') === false
                    && strpos($text, 'Title: My post') !== false;
            }),
            $this->callback(fn($schema) => $schema['required'] === ['short_description']),
            $this->callback(fn($o) => $o['max_tokens'] === 400 && $o['max_retries'] === 0 && $o['source'] === 'blog')
        )->willReturn($result);

        $out = $this->assist->generate('short', 'My post', 'Body {{widget type="x"}} text', [], [], 1);
        $this->assertSame(['short_description' => 'Hi there'], $out);
    }

    public function testTaxonomyMatchesExisting(): void
    {
        $this->respond(['tags' => ['magento', 'new tag'], 'topics' => ['News']]);
        $out = $this->assist->generate(
            'taxonomy',
            'T',
            '',
            [['id' => 1, 'name' => 'Magento']],
            [['id' => 9, 'name' => 'news']],
            1
        );
        $this->assertSame([['id' => 1, 'name' => 'Magento']], $out['tags']['existing']);
        $this->assertSame(['new tag'], $out['tags']['new']);
        $this->assertSame([['id' => 9, 'name' => 'news']], $out['topics']['existing']);
    }

    public function testTitles(): void
    {
        $this->respond(['titles' => ['A', 'a', 'B']]);
        $this->assertSame(['titles' => ['A', 'B']], $this->assist->generate('titles', 'T', '', [], [], 1));
    }

    public function testEmptyInputNeverCallsClient(): void
    {
        $this->client->expects($this->never())->method('complete');
        $this->expectException(LocalizedException::class);
        $this->assist->generate('short', '', 'x', [], [], 1);
    }

    public function testMissingKeysIsInvalidResponse(): void
    {
        $this->respond(['other' => 1]);
        $this->expectExceptionMessage('Invalid AI response.');
        $this->assist->generate('titles', 'T', '', [], [], 1);
    }

    public function testAiExceptionPropagates(): void
    {
        $this->client->method('complete')->willThrowException(AiException::create(AiException::QUOTA));
        $this->expectException(AiException::class);
        $this->assist->generate('titles', 'T', '', [], [], 1);
    }

    /**
     * @param string $type
     * @dataProvider schemaProvider
     */
    #[DataProvider('schemaProvider')]
    public function testSchemaRequiresAllKeys(string $type, array $keys): void
    {
        $this->assertSame($keys, $this->assist->schema($type)['required']);
    }

    /**
     * @return array
     */
    public static function schemaProvider(): array
    {
        return [
            'short' => ['short', ['short_description']],
            'taxonomy' => ['taxonomy', ['tags', 'topics']],
            'titles' => ['titles', ['titles']],
        ];
    }
}
