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

use Magento\Framework\DataObject;
use Mageplaza\Blog\Model\Config\Source\WidgetCategory;
use Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WidgetCategoryTest extends TestCase
{
    private WidgetCategory $model;
    private CollectionFactory|MockObject $collectionFactoryMock;

    protected function setUp(): void
    {
        $this->collectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->model = new WidgetCategory($this->collectionFactoryMock);
    }

    public function testToOptionArraySkipsRootCategory(): void
    {
        $items = [
            new DataObject(['id' => '1', 'name' => 'Root']),
            new DataObject(['id' => '2', 'name' => 'News']),
            new DataObject(['id' => '3', 'name' => 'Tips']),
        ];

        $collection = new class ($items) {
            public function __construct(private $items)
            {
            }

            public function getItems()
            {
                return $this->items;
            }
        };
        $this->collectionFactoryMock->method('create')->willReturn($collection);

        $result = $this->model->toOptionArray();

        $this->assertSame([
            ['value' => '2', 'label' => 'News'],
            ['value' => '3', 'label' => 'Tips'],
        ], $result);
    }
}
