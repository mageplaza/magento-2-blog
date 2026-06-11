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

namespace Mageplaza\Blog\Test\Unit\Model\Config\Source\Comments;

use Mageplaza\Blog\Model\Config\Source\Comments\Type;
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
        $this->assertSame(1, Type::DEFAULT_COMMENT);
        $this->assertSame(2, Type::FACEBOOK);
        $this->assertSame(3, Type::DISQUS);
        $this->assertSame(4, Type::DISABLE);
    }

    public function testToArrayKeys(): void
    {
        $array = $this->model->toArray();

        $this->assertCount(4, $array);
        $this->assertArrayHasKey(Type::DEFAULT_COMMENT, $array);
        $this->assertArrayHasKey(Type::DISABLE, $array);
    }

    public function testToOptionArrayFormat(): void
    {
        $result = $this->model->toOptionArray();

        $this->assertCount(4, $result);
        foreach ($result as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
    }
}
