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

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Model\Ai\Assist;
use Mageplaza\Blog\Model\Ai\OutputFilter;
use Mageplaza\Blog\Model\Ai\PostStore;
use Mageplaza\Blog\Model\Ai\Service;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AssistTest extends TestCase
{
    /**
     * @var Assist
     */
    private $assist;

    protected function setUp(): void
    {
        $this->assist = new Assist(new OutputFilter(), $this->createMock(Service::class));
    }

    public function testPrepareInputCleansAndCuts(): void
    {
        $content = '<p>Hello</p>{{widget type="x"}}' . str_repeat('a', 7000);
        $result = $this->assist->prepareInput(' Name ', $content);
        $this->assertSame('Name', $result['name']);
        $this->assertSame(6000, mb_strlen($result['content']));
        $this->assertStringNotContainsString('{{', $result['content']);
        $this->assertStringNotContainsString('<p>', $result['content']);
    }

    public function testPrepareInputEmptyThrows(): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Write some content first.');
        $this->assist->prepareInput('', '{{widget type="x"}} short');
    }

    public function testPrepareInputNameOnlyIsAllowed(): void
    {
        $this->assertSame('Title', $this->assist->prepareInput('Title', '')['name']);
    }

    public function testTaxonomyMatchesCaseInsensitively(): void
    {
        $tags = [['id' => 1, 'name' => 'Magento'], ['id' => 2, 'name' => 'SEO']];
        $topics = [['id' => 7, 'name' => 'News']];
        $result = $this->assist->parse(
            'taxonomy',
            ['tags' => ['magento', 'seo', 'Unknown', 'MAGENTO'], 'topics' => ['news', 'Other']],
            $tags,
            $topics
        );
        $this->assertSame([['id' => 1, 'name' => 'Magento'], ['id' => 2, 'name' => 'SEO']], $result['tags']['existing']);
        $this->assertSame(['Unknown'], $result['tags']['new']);
        $this->assertSame([['id' => 7, 'name' => 'News']], $result['topics']['existing']);
        $this->assertSame(['Other'], $result['topics']['new']);
    }

    public function testTaxonomyMissingKeyIsSkipped(): void
    {
        $result = $this->assist->parse('taxonomy', ['tags' => ['a']]);
        $this->assertArrayHasKey('tags', $result);
        $this->assertArrayNotHasKey('topics', $result);
    }

    public function testTaxonomyLimits(): void
    {
        $names = ['a', 'b', 'c', 'd', 'e', 'f', 'g'];
        $result = $this->assist->parse('taxonomy', ['tags' => $names, 'topics' => $names]);
        $this->assertCount(5, $result['tags']['new']);
        $this->assertCount(3, $result['topics']['new']);
    }

    public function testShortIsCutAndStripped(): void
    {
        $result = $this->assist->parse('short', ['short_description' => '<b>' . str_repeat('x', 500) . '</b>']);
        $this->assertSame(300, mb_strlen($result['short_description']));
        $this->assertStringNotContainsString('<', $result['short_description']);
    }

    public function testTitlesDedupAndCut(): void
    {
        $result = $this->assist->parse(
            'titles',
            ['titles' => ['One', 'one', str_repeat('t', 90), 'Two', 'Three', 'Four', 'Five']]
        );
        $this->assertSame(['One', str_repeat('t', 70), 'Two', 'Three', 'Four'], $result['titles']);
    }

    /**
     * @dataProvider invalidProvider
     */
    #[DataProvider('invalidProvider')]
    public function testInvalidResponseThrows(string $type, array $data): void
    {
        $this->expectException(LocalizedException::class);
        $this->expectExceptionMessage('Invalid AI response.');
        $this->assist->parse($type, $data);
    }

    public static function invalidProvider(): array
    {
        return [
            'short missing' => ['short', ['other' => 'x']],
            'taxonomy missing' => ['taxonomy', []],
        ];
    }

    public function testEmptyValidResultsDoNotThrow(): void
    {
        $this->assertSame([], $this->assist->parse('titles', ['titles' => []]));
        $this->assertSame([], $this->assist->parse('short', ['short_description' => '']));
    }

    public function testPrepareInputSpacesBlocksAndDecodes(): void
    {
        $result = $this->assist->prepareInput('N', "<p>One</p><p>Two&nbsp;&amp;\n\n  three</p>" . str_repeat('a', 7000));
        $this->assertStringStartsWith('One Two', $result['content']);
        $this->assertStringContainsString('& three', $result['content']);
        $this->assertSame(6000, mb_strlen($result['content']));
    }

    /**
     * @dataProvider storeProvider
     */
    #[DataProvider('storeProvider')]
    public function testPostStore($input, int $expected): void
    {
        $store = $this->createMock(StoreInterface::class);
        $store->method('getId')->willReturn(9);
        $manager = $this->createMock(StoreManagerInterface::class);
        $manager->method('getDefaultStoreView')->willReturn($store);
        $this->assertSame($expected, (new PostStore($manager))->getStoreId($input));
    }

    public static function storeProvider(): array
    {
        return [
            'first non zero' => [[0, '3', 5], 3],
            'comma string' => ['0,4', 4],
            'all stores' => [[0], 9],
            'empty' => ['', 9],
        ];
    }
}
