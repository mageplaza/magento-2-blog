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

namespace Mageplaza\Blog\Test\Unit\Model\Config\Source\Import;

use Mageplaza\Blog\Model\Config\Source\Import\Type;
use PHPUnit\Framework\TestCase;

class TypeTest extends TestCase
{
    private Type $model;

    protected function setUp(): void
    {
        $this->model = new Type();
    }

    public function testConstants(): void
    {
        $this->assertSame('wordpress', Type::WORDPRESS);
        $this->assertSame('aheadworksm1', Type::AHEADWORK);
        $this->assertSame('magefan', Type::MAGEFAN);
    }

    public function testToArrayKeys(): void
    {
        $array = $this->model->toArray();

        $this->assertArrayHasKey(Type::WORDPRESS, $array);
        $this->assertArrayHasKey(Type::AHEADWORK, $array);
        $this->assertArrayHasKey(Type::MAGEFAN, $array);
    }

    public function testToOptionArrayFormat(): void
    {
        $result = $this->model->toOptionArray();

        $this->assertNotEmpty($result);
        foreach ($result as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }
}
