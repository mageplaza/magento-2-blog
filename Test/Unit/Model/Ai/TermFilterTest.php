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

use Mageplaza\Blog\Model\Ai\TermFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TermFilterTest extends TestCase
{
    /**
     * @var TermFilter
     */
    private $filter;

    protected function setUp(): void
    {
        $this->filter = new TermFilter();
    }

    /**
     * @return array
     */
    public static function badTermProvider(): array
    {
        return [
            ['john@example.com'],
            ['+84 912 345 678'],
            ['(028) 3822 1234'],
            ['https://evil.test/x'],
            ['www.shop.vn'],
            ['shop.com'],
            ['<b>bold</b>'],
            ['{{var x}}'],
            ['one two three four five six seven eight nine'],
            [str_repeat('a', 81)],
            ['1 union select password'],
            ['select * from admin_user'],
            ['alert script'],
            ['<script>'],
        ];
    }

    /**
     * @param string $term
     * @dataProvider badTermProvider
     */
    #[DataProvider('badTermProvider')]
    public function testBadTermsRemoved(string $term): void
    {
        $result = $this->filter->filter([['query_text' => $term, 'popularity' => 3]]);

        $this->assertSame([], $result['terms']);
        $this->assertSame(1, $result['removed']);
    }

    public function testScriptSubstringWordsKept(): void
    {
        $result = (new TermFilter())->filter([
            ['query_text' => 'prescription glasses', 'popularity' => 1],
            ['query_text' => 'subscription box', 'popularity' => 1],
            ['query_text' => 'description', 'popularity' => 1]
        ]);

        $this->assertCount(3, $result['terms']);
        $this->assertSame(0, $result['removed']);
    }

    public function testGoodTermsKeptWithVietnamese(): void
    {
        $result = $this->filter->filter([
            ['query_text' => 'áo thun nam', 'popularity' => 5],
            ['query_text' => 'Giày thể thao', 'popularity' => 2],
            ['query_text' => 'order 123456', 'popularity' => 1],
        ]);

        $this->assertSame(['áo thun nam', 'Giày thể thao', 'order 123456'], array_column($result['terms'], 'query_text'));
        $this->assertSame(0, $result['removed']);
    }

    public function testDuplicatesMergedAndPopularitySummed(): void
    {
        $result = $this->filter->filter([
            ['query_text' => 'Áo Thun', 'popularity' => 5],
            ['query_text' => ' áo thun ', 'popularity' => 3],
        ]);

        $this->assertCount(1, $result['terms']);
        $this->assertSame(8, $result['terms'][0]['popularity']);
        $this->assertSame(0, $result['removed']);
    }
}
