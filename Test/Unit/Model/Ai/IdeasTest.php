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

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Mageplaza\Blog\Model\Ai\Ideas;
use Mageplaza\Blog\Model\Ai\TermFilter;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IdeasTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $select;

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
        $this->select = $this->createMock(Select::class);
        foreach (['from', 'where', 'order', 'limit'] as $method) {
            $this->select->method($method)->willReturnSelf();
        }
        $this->connection = $this->createMock(AdapterInterface::class);
        $this->connection->method('select')->willReturn($this->select);
        $resource = $this->createMock(ResourceConnection::class);
        $resource->method('getConnection')->willReturn($this->connection);
        $resource->method('getTableName')->willReturnArgument(0);
        $this->ideas = new Ideas(
            $resource,
            new TermFilter(),
            $this->createMock(\Magento\Framework\App\CacheInterface::class),
            new \Mageplaza\Blog\Model\Ai\OutputFilter(),
            $this->createMock(\Mageplaza\Blog\Model\Ai\Service::class)
        );
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

    public function testNotEnoughTerms(): void
    {
        $this->connection->method('fetchOne')->willReturn(false);
        $this->connection->method('fetchAll')->willReturnOnConsecutiveCalls($this->rows(5), $this->rows(4));

        $result = $this->ideas->loadTerms(1);

        $this->assertFalse($result['enough']);
        $this->assertCount(5, $result['popular']);
        $this->assertCount(4, $result['zero_result']);
    }

    public function testEnoughTermsAndRemovedCount(): void
    {
        $this->connection->method('fetchOne')->willReturn(false);
        $popular = array_merge($this->rows(8), [['query_text' => 'a@b.co', 'popularity' => 9]]);
        $this->connection->method('fetchAll')->willReturnOnConsecutiveCalls($popular, $this->rows(2));

        $result = $this->ideas->loadTerms(1);

        $this->assertTrue($result['enough']);
        $this->assertSame(1, $result['removed']);
    }

    public function testOffsetPassedToLimit(): void
    {
        $this->connection->method('fetchOne')->willReturn(false);
        $this->connection->method('fetchAll')->willReturn([]);
        $calls = [];
        $this->select->method('limit')->willReturnCallback(function ($count, $offset) use (&$calls) {
            $calls[] = [$count, $offset];

            return $this->select;
        });

        $this->ideas->loadTerms(1, 150);

        $this->assertSame([[1, 1000], [75, 150], [75, 150]], $calls);
    }

    public function testLargeTableAddsPopularityThreshold(): void
    {
        $this->connection->method('fetchOne')->willReturn('1');
        $this->connection->method('fetchAll')->willReturn([]);
        $this->select->expects($this->atLeastOnce())->method('where')->willReturnCallback(
            function (string $condition) {
                $this->assertNotSame('', $condition);

                return $this->select;
            }
        );

        $this->assertFalse($this->ideas->loadTerms(1)['enough']);
    }
}
