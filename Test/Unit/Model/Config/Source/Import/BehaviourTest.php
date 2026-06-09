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

use Mageplaza\Blog\Model\Config\Source\Import\Behaviour;
use PHPUnit\Framework\TestCase;

class BehaviourTest extends TestCase
{
    private Behaviour $model;

    protected function setUp(): void
    {
        $this->model = new Behaviour();
    }

    public function testConstants(): void
    {
        $this->assertSame('update', Behaviour::UPDATE);
        $this->assertSame('replace', Behaviour::REPLACE);
        $this->assertSame('delete', Behaviour::DELETE);
    }

    public function testToArrayKeys(): void
    {
        $array = $this->model->toArray();

        $this->assertArrayHasKey(Behaviour::UPDATE, $array);
        $this->assertArrayHasKey(Behaviour::REPLACE, $array);
        $this->assertArrayHasKey(Behaviour::DELETE, $array);
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
