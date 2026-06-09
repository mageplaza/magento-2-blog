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

use Mageplaza\Blog\Model\Config\Source\AuthorType;
use PHPUnit\Framework\TestCase;

class AuthorTypeTest extends TestCase
{
    private AuthorType $model;

    protected function setUp(): void
    {
        $this->model = new AuthorType();
    }

    public function testConstants(): void
    {
        $this->assertSame('0', AuthorType::ADMIN);
        $this->assertSame('1', AuthorType::CUSTOMER);
    }

    public function testToArrayKeys(): void
    {
        $array = $this->model->toArray();

        $this->assertCount(2, $array);
        $this->assertArrayHasKey(AuthorType::ADMIN, $array);
        $this->assertArrayHasKey(AuthorType::CUSTOMER, $array);
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
