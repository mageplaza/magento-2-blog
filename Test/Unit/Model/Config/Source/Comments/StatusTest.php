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

use Mageplaza\Blog\Model\Config\Source\Comments\Status;
use PHPUnit\Framework\TestCase;

class StatusTest extends TestCase
{
    private Status $model;

    protected function setUp(): void
    {
        $this->model = new Status();
    }

    public function testConstants(): void
    {
        $this->assertSame(1, Status::APPROVED);
        $this->assertSame(2, Status::SPAM);
        $this->assertSame(3, Status::PENDING);
    }

    public function testToArrayKeys(): void
    {
        $array = $this->model->toArray();

        $this->assertCount(3, $array);
        $this->assertArrayHasKey(Status::APPROVED, $array);
        $this->assertArrayHasKey(Status::SPAM, $array);
        $this->assertArrayHasKey(Status::PENDING, $array);
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
}
