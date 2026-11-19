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

use ArrayIterator;
use Magento\Framework\DataObject;
use Mageplaza\Blog\Model\ResourceModel\Author\CollectionFactory as AuthorFactory;
use Mageplaza\Blog\Model\Config\Source\Author;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthorTest extends TestCase
{
    private Author $model;
    private AuthorFactory|MockObject $authorFactoryMock;

    protected function setUp(): void
    {
        $this->authorFactoryMock = $this->createStub(AuthorFactory::class);

        $this->model = new Author($this->authorFactoryMock);
    }

    public function testToOptionArrayMapsAuthors(): void
    {
        $authors = new ArrayIterator([
            5 => new DataObject(['name' => 'John Doe']),
            8 => new DataObject(['name' => 'Jane Roe']),
        ]);

        $collection = new class ($authors) {
            public function __construct(private $authors)
            {
            }

            public function addFieldToFilter($field, $value)
            {
                return $this->authors;
            }
        };

        $this->authorFactoryMock->method('create')->willReturn($collection);

        $result = $this->model->toOptionArray();

        $this->assertSame([
            ['value' => 5, 'label' => 'John Doe'],
            ['value' => 8, 'label' => 'Jane Roe'],
        ], $result);
    }
}
