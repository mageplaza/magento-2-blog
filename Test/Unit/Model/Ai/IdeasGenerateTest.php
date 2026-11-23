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

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AiIntegration\Api\AiClientInterface;
use Mageplaza\AiIntegration\Api\ConnectionStatusInterface;
use Mageplaza\AiIntegration\Api\Data\ResultInterface;
use Mageplaza\AiIntegration\Api\ModelListInterface;
use Mageplaza\AiIntegration\Helper\Data as AiHelper;
use Mageplaza\AiIntegration\Exception\AiException;
use Mageplaza\Blog\Model\Ai\Ideas;
use Mageplaza\Blog\Model\Ai\OutputFilter;
use Mageplaza\Blog\Model\Ai\Service;
use Mageplaza\Blog\Model\Ai\TermFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IdeasGenerateTest extends TestCase
{
    /**
     * @var AiClientInterface|MockObject
     */
    private $client;

    /**
     * @var CacheInterface|MockObject
     */
    private $cache;

    /**
     * @var MockObject
     */
    private $connection;

    /**
     * @var Ideas
     */
    private $ideas;

    protected function setUp(): void
    {
        $select = $this->createMock(Select::class);
        foreach (['from', 'where', 'order', 'limit'] as $method) {
            $select->method($method)->willReturnSelf();
        }
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->connection->method('select')->willReturn($select);
        $this->connection->method('fetchOne')->willReturn(false);
        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($this->connection);
        $resource->method('getTableName')->willReturnArgument(0);

        $this->client = $this->createMock(AiClientInterface::class);
        $this->cache = $this->createMock(CacheInterface::class);
        $service = new Service(
            $this->client,
            $this->createMock(ConnectionStatusInterface::class),
            $this->createMock(ModelListInterface::class),
            $this->createMock(ScopeConfigInterface::class),
            $this->createMock(AiHelper::class)
        );
        $this->ideas = new Ideas($resource, new TermFilter(), $this->cache, new OutputFilter(), $service);
    }

    /**
     * @param int $count
     * @return array
     */
    private function rows(int $count): array
    {
        $rows = [];
        for ($i = 1; $i <= $count; $i++) {
            $rows[] = ['query_text' => 'term ' . $i, 'popularity' => $i];
        }

        return $rows;
    }

    private function terms(int $popular, int $zero): void
    {
        $this->connection->method('fetchAll')->willReturnOnConsecutiveCalls($this->rows($popular), $this->rows($zero));
    }

    /**
     * @param array $data
     */
    private function aiReturns(array $data): void
    {
        $result = $this->createMock(ResultInterface::class);
        $result->method('getData')->willReturn($data);
        $this->client->expects($this->once())->method('complete')->willReturn($result);
    }

    public function testFewTermsDoNotCallClient(): void
    {
        $this->terms(5, 4);
        $this->client->expects($this->never())->method('complete');
        $this->cache->expects($this->never())->method('save');

        $this->expectException(LocalizedException::class);
        $this->ideas->generate(1, 10);
    }

    public function testSuccessFiltersSortsAndCuts(): void
    {
        $this->terms(8, 4);
        $topics = [
            ['title' => 'Low', 'angle' => 'a', 'intent' => 'guide', 'terms' => ['term 1']],
            ['title' => 'High <b>x</b>', 'angle' => '{{block id="1"}}b', 'intent' => 'bogus',
                'terms' => ['TERM 8', 'term 7', 'unknown', 'term 8']],
            ['title' => '', 'angle' => 'empty', 'intent' => 'guide', 'terms' => ['term 2']]
        ];
        $this->aiReturns(['topics' => $topics]);
        $this->cache->expects($this->once())->method('save');

        $result = $this->ideas->generate(1, 1);

        $this->assertFalse($result['cached']);
        $this->assertCount(1, $result['topics']);
        $this->assertSame('High x', $result['topics'][0]['title']);
        $this->assertSame('b', $result['topics'][0]['angle']);
        $this->assertSame('other', $result['topics'][0]['intent']);
        $this->assertSame(['term 8', 'term 7'], $result['topics'][0]['terms']);
        $this->assertSame(15, $result['topics'][0]['total_searches']);
        $this->assertFalse($result['topics'][0]['has_zero_result']);
    }

    public function testZeroResultFlag(): void
    {
        $this->connection->method('fetchAll')->willReturnOnConsecutiveCalls(
            $this->rows(8),
            [['query_text' => 'nothing', 'popularity' => 5], ['query_text' => 'none', 'popularity' => 4]]
        );
        $this->aiReturns(['topics' => [
            ['title' => 'T', 'angle' => 'a', 'intent' => 'how_to', 'terms' => ['nothing', 'term 1']]
        ]]);

        $topic = $this->ideas->generate(1, 10)['topics'][0];

        $this->assertTrue($topic['has_zero_result']);
        $this->assertSame(6, $topic['total_searches']);
    }

    public function testEmptyTopicsNotCached(): void
    {
        $this->terms(8, 4);
        $this->aiReturns(['topics' => []]);
        $this->cache->expects($this->never())->method('save');

        $this->assertSame([], $this->ideas->generate(1, 10)['topics']);
    }

    public function testCacheHitSkipsClient(): void
    {
        $this->terms(8, 4);
        $this->cache->method('load')->willReturn(json_encode(['topics' => [['title' => 'C']], 'removed' => 2]));
        $this->client->expects($this->never())->method('complete');

        $result = $this->ideas->generate(1, 10);

        $this->assertTrue($result['cached']);
        $this->assertSame('C', $result['topics'][0]['title']);
    }

    public function testRefreshBypassesCache(): void
    {
        $this->terms(8, 4);
        $this->cache->expects($this->never())->method('load');
        $this->aiReturns(['topics' => []]);

        $this->ideas->generate(1, 10, 0, true);
    }

    public function testCacheKeyHasOffset(): void
    {
        $keys = [];
        $this->connection->method('fetchAll')->willReturn($this->rows(8));
        $this->cache->method('load')->willReturnCallback(function ($key) use (&$keys) {
            $keys[] = $key;

            return json_encode(['topics' => [], 'removed' => 0]);
        });

        $this->ideas->generate(1, 10, 0);
        $this->ideas->generate(1, 10, 150);

        $this->assertNotSame($keys[0], $keys[1]);
    }

    public function testTopicWithoutMatchingTermsDropped(): void
    {
        $this->terms(8, 4);
        $this->aiReturns(['topics' => [
            ['title' => 'Ghost', 'angle' => 'a', 'intent' => 'guide', 'terms' => ['unknown']]
        ]]);

        $this->assertSame([], $this->ideas->generate(1, 10)['topics']);
    }

    public function testTokenBudgetAndTermCap(): void
    {
        $this->terms(8, 4);
        $result = $this->createMock(ResultInterface::class);
        $result->method('getData')->willReturn(['topics' => []]);
        $this->client->expects($this->once())->method('complete')->willReturnCallback(
            function ($messages, $schema, $options) use ($result) {
                $this->assertSame(3000, $options['max_tokens']);
                $this->assertSame(
                    Ideas::MAX_TOPIC_TERMS,
                    $schema['properties']['topics']['items']['properties']['terms']['maxItems']
                );

                return $result;
            }
        );

        $this->ideas->generate(1, 10);
    }

    public function testAiExceptionPropagates(): void
    {
        $this->terms(8, 4);
        $this->client->method('complete')->willThrowException(AiException::create(AiException::QUOTA));
        $this->cache->expects($this->never())->method('save');

        $this->expectException(AiException::class);
        $this->ideas->generate(1, 10);
    }
}
