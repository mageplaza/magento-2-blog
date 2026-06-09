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

namespace Mageplaza\Blog\Test\Unit\Model\Config\Source\Comments\Facebook;

use Mageplaza\Blog\Model\Config\Source\Comments\Facebook\Orderby;
use PHPUnit\Framework\TestCase;

class OrderbyTest extends TestCase
{
    private Orderby $model;

    protected function setUp(): void
    {
        $this->model = new Orderby();
    }

    public function testConstants(): void
    {
        $this->assertSame('social', Orderby::SOCIAL);
        $this->assertSame('reverse_time', Orderby::REVERSE_TIME);
        $this->assertSame('time', Orderby::TIME);
    }

    public function testToArrayKeys(): void
    {
        $array = $this->model->toArray();

        $this->assertCount(3, $array);
        $this->assertArrayHasKey(Orderby::SOCIAL, $array);
        $this->assertArrayHasKey(Orderby::REVERSE_TIME, $array);
        $this->assertArrayHasKey(Orderby::TIME, $array);
    }

    public function testToOptionArrayFormat(): void
    {
        $result = $this->model->toOptionArray();

        $this->assertCount(3, $result);
        foreach ($result as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }

    public function testGetAllOptionsEqualsToOptionArray(): void
    {
        $this->assertEquals($this->model->toOptionArray(), $this->model->getAllOptions());
    }
}
