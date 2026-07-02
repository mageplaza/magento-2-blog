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

use Mageplaza\Blog\Model\Config\Source\AuthorStatus;
use PHPUnit\Framework\TestCase;

class AuthorStatusTest extends TestCase
{
    private AuthorStatus $model;

    protected function setUp(): void
    {
        $this->model = new AuthorStatus();
    }

    public function testConstants(): void
    {
        $this->assertSame('0', AuthorStatus::PENDING);
        $this->assertSame('1', AuthorStatus::APPROVED);
        $this->assertSame('2', AuthorStatus::DISAPPROVED);
    }

    public function testToArrayKeys(): void
    {
        $array = $this->model->toArray();

        $this->assertCount(3, $array);
        $this->assertArrayHasKey(AuthorStatus::PENDING, $array);
        $this->assertArrayHasKey(AuthorStatus::APPROVED, $array);
        $this->assertArrayHasKey(AuthorStatus::DISAPPROVED, $array);
    }

    public function testToOptionArrayFormat(): void
    {
        $result = $this->model->toOptionArray();

        $this->assertCount(3, $result);
        foreach ($result as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }
        // numeric-string array keys are coerced to int by PHP, so compare loosely
        $this->assertEquals(AuthorStatus::PENDING, $result[0]['value']);
    }
}
