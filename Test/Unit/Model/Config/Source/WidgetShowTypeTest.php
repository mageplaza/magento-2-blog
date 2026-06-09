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

namespace Mageplaza\Blog\Test\Unit\Model\Config\Source;

use Mageplaza\Blog\Model\Config\Source\WidgetShowType;
use PHPUnit\Framework\TestCase;

class WidgetShowTypeTest extends TestCase
{
    private WidgetShowType $model;

    protected function setUp(): void
    {
        $this->model = new WidgetShowType();
    }

    public function testToOptionArray(): void
    {
        $result = $this->model->toOptionArray();

        $this->assertCount(2, $result);
        $this->assertSame('new', $result[0]['value']);
        $this->assertSame('category', $result[1]['value']);
        foreach ($result as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }
}
