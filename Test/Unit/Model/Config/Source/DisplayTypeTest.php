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

use Mageplaza\Blog\Model\Config\Source\DisplayType;
use PHPUnit\Framework\TestCase;

class DisplayTypeTest extends TestCase
{
    private DisplayType $model;

    protected function setUp(): void
    {
        $this->model = new DisplayType();
    }

    public function testConstants(): void
    {
        $this->assertSame(1, DisplayType::LIST_VIEW);
        $this->assertSame(2, DisplayType::GRID);
    }

    public function testToArrayKeys(): void
    {
        $array = $this->model->toArray();

        $this->assertCount(2, $array);
        $this->assertArrayHasKey(DisplayType::LIST_VIEW, $array);
        $this->assertArrayHasKey(DisplayType::GRID, $array);
    }

    public function testToOptionArrayFormat(): void
    {
        $result = $this->model->toOptionArray();

        $this->assertCount(2, $result);
        foreach ($result as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }
}
