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

use Mageplaza\Blog\Model\Ai\OutputFilter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class OutputFilterTest extends TestCase
{
    /**
     * @var OutputFilter
     */
    private $filter;

    protected function setUp(): void
    {
        $this->filter = new OutputFilter();
    }

    /**
     * @return array
     */
    public static function directiveProvider(): array
    {
        return [
            ['{{widget type="x"}}'],
            ['{{block class="x"}}'],
            ['{{var config.x}}'],
            ['{{ orphan'],
            ['{{a{{b}}c}}'],
            ['{{{{var x}}var x}}'],
            ['{<b>{var x}}</b>'],
        ];
    }

    /**
     * @param string $input
     */
    #[DataProvider('directiveProvider')]
    public function testDirectivesRemoved(string $input): void
    {
        foreach ([$this->filter->filterHtml('<p>' . $input . '</p>'), $this->filter->filterText($input)] as $out) {
            $this->assertStringNotContainsString('{{', $out);
            $this->assertStringNotContainsString('{{var', $out);
        }
    }

    public function testDisallowedTagsUnwrapped(): void
    {
        $out = $this->filter->filterHtml(
            '<div><img src="x.png"><iframe src="http://e.com">frame</iframe><p>Keep</p></div>'
        );
        $this->assertSame('frame<p>Keep</p>', $out);
    }

    public function testScriptAndStyleRemovedWithContent(): void
    {
        $out = $this->filter->filterHtml('<p>a</p><script>alert(1)</script><style>p{}</style>');
        $this->assertSame('<p>a</p>', $out);
    }

    public function testH1UnwrappedAndFenceRemoved(): void
    {
        $out = $this->filter->filterHtml("```html\n<h1>Title</h1><h2>Sub</h2>\n```");
        $this->assertSame('Title<h2>Sub</h2>', $out);
    }

    public function testLinkKeepsOnlyHref(): void
    {
        $out = $this->filter->filterHtml('<a href="https://e.com/x" onclick="x()" class="c">L</a>');
        $this->assertSame('<a href="https://e.com/x">L</a>', $out);
        $this->assertSame('<a href="/page">L</a>', $this->filter->filterHtml('<a href="/page" target="_blank">L</a>'));
    }

    public function testDangerousHrefDropped(): void
    {
        foreach (['javascript:alert(1)', ' JaVa	script:alert(1)', 'data:text/html,x', 'vbscript:x', '//evil.com'] as $href) {
            $out = $this->filter->filterHtml('<a href="' . $href . '">L</a>');
            $this->assertSame('L', $out, $href);
        }
    }

    public function testAllowlistTagsKept(): void
    {
        $html = '<h2>A</h2><h3>B</h3><p><strong>s</strong><em>e</em></p><ul><li>x</li></ul><ol><li>y</li></ol>'
            . '<table><thead><tr><th>h</th></tr></thead><tbody><tr><td>d</td></tr></tbody></table>'
            . '<blockquote>q</blockquote>';
        $this->assertSame($html, $this->filter->filterHtml($html));
    }

    public function testUtf8Preserved(): void
    {
        $this->assertSame('<p>Tiếng Việt đẹp &amp; rõ</p>', $this->filter->filterHtml('<p>Tiếng Việt đẹp & rõ</p>'));
        $this->assertSame('Đà Nẵng', $this->filter->filterText(' <b>Đà Nẵng</b> '));
    }

    public function testFilterTextStripsTags(): void
    {
        $this->assertSame('Hello world', $this->filter->filterText("<p>Hello</p> world{{var x}}\n"));
    }
}
