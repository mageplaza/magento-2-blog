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

namespace Mageplaza\Blog\Test\Unit\Model\Config\Source\DateFormat;

use Mageplaza\Blog\Model\Config\Source\DateFormat\TypeMonth;
use PHPUnit\Framework\TestCase;

class TypeMonthTest extends TestCase
{
    private TypeMonth $model;

    protected function setUp(): void
    {
        $this->model = new TypeMonth();
    }

    public function testToOptionArrayFormat(): void
    {
        $result = $this->model->toOptionArray();

        $this->assertCount(4, $result);
        foreach ($result as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
            $this->assertStringStartsWith($option['value'], $option['label']);
        }
        $this->assertSame('F , Y', $result[0]['value']);
    }
}
