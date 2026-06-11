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

namespace Mageplaza\Blog\Test\Unit\Helper;

use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\Config\Source\SideBarLR;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DataTest extends TestCase
{
    /**
     * @return Data|MockObject
     */
    private function createHelper(array $methods = [])
    {
        return $this->getMockBuilder(Data::class)
            ->disableOriginalConstructor()
            ->onlyMethods($methods)
            ->getMock();
    }

    public function testConfigModulePath(): void
    {
        $this->assertSame('blog', Data::CONFIG_MODULE_PATH);
    }

    public function testGetBlogConfigPrefixesModulePath(): void
    {
        $helper = $this->createHelper(['getConfigValue']);
        $helper->method('getConfigValue')->with('blog/display/name', null)->willReturn('My Blog');

        $this->assertSame('My Blog', $helper->getBlogConfig('display/name'));
    }

    public function testGetBlogConfigWithEmptyCode(): void
    {
        $helper = $this->createHelper(['getConfigValue']);
        $helper->method('getConfigValue')->with('blog', null)->willReturn('value');

        $this->assertSame('value', $helper->getBlogConfig(''));
    }

    public function testGetSidebarLayoutLeft(): void
    {
        $helper = $this->createHelper(['getConfigValue']);
        $helper->method('getConfigValue')->willReturn(0);

        $this->assertSame(SideBarLR::LEFT, $helper->getSidebarLayout());
    }

    public function testGetSidebarLayoutRight(): void
    {
        $helper = $this->createHelper(['getConfigValue']);
        $helper->method('getConfigValue')->willReturn(1);

        $this->assertSame(SideBarLR::RIGHT, $helper->getSidebarLayout());
    }

    public function testGetSidebarLayoutPassthrough(): void
    {
        $helper = $this->createHelper(['getConfigValue']);
        $helper->method('getConfigValue')->willReturn('1column');

        $this->assertSame('1column', $helper->getSidebarLayout());
    }

    public function testGetDisplayConfigPrefix(): void
    {
        $helper = $this->createHelper(['getBlogConfig']);
        $helper->method('getBlogConfig')->with('display/foo', null)->willReturn('bar');

        $this->assertSame('bar', $helper->getDisplayConfig('foo'));
    }

    public function testGetRouteDefault(): void
    {
        $helper = $this->createHelper(['getDisplayConfig']);
        $helper->method('getDisplayConfig')->with('url_prefix', null)->willReturn('');

        $this->assertSame('blog', $helper->getRoute());
    }

    public function testGetRouteCustom(): void
    {
        $helper = $this->createHelper(['getDisplayConfig']);
        $helper->method('getDisplayConfig')->with('url_prefix', null)->willReturn('news');

        $this->assertSame('news', $helper->getRoute());
    }

    public function testGetUrlSuffixWithValue(): void
    {
        $helper = $this->createHelper(['getDisplayConfig']);
        $helper->method('getDisplayConfig')->with('url_suffix', null)->willReturn('html');

        $this->assertSame('.html', $helper->getUrlSuffix());
    }

    public function testGetUrlSuffixEmpty(): void
    {
        $helper = $this->createHelper(['getDisplayConfig']);
        $helper->method('getDisplayConfig')->with('url_suffix', null)->willReturn('');

        $this->assertSame('', $helper->getUrlSuffix());
    }

    public function testGetBlogNameDefault(): void
    {
        $helper = $this->createHelper(['getDisplayConfig']);
        $helper->method('getDisplayConfig')->with('name', null)->willReturn('');

        $this->assertEquals(__('Blog'), $helper->getBlogName());
    }

    public function testGetBlogNameCustom(): void
    {
        $helper = $this->createHelper(['getDisplayConfig']);
        $helper->method('getDisplayConfig')->with('name', null)->willReturn('Cool Blog');

        $this->assertSame('Cool Blog', $helper->getBlogName());
    }

    public function testGetDateFormatAppliesConfigFormat(): void
    {
        $helper = $this->createHelper(['getTimezone', 'getBlogConfig']);
        $helper->method('getTimezone')->willReturn('UTC');
        $helper->method('getBlogConfig')->with('display/date_type')->willReturn('Y-m-d');

        $this->assertSame('2025-06-01', $helper->getDateFormat('2025-06-01 10:00:00'));
    }
}
