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

use Mageplaza\Blog\Model\Config\Source\DateFormat\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    private Type $model;

    protected function setUp(): void
    {
        $this->model = new Type();
    }

    public function testToOptionArrayFormat(): void
    {
        $result = $this->model->toOptionArray();

        $this->assertCount(12, $result);
        foreach ($result as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
            // label is "<format> (<rendered date>)" so it starts with the format value
            $this->assertStringStartsWith($option['value'], $option['label']);
        }
        $this->assertSame('F j, Y', $result[0]['value']);
    }
}
