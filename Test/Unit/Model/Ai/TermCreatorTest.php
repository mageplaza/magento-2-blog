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

namespace Mageplaza\Blog\Test\Unit\Model\Ai;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Model\Ai\TermCreator;
use Mageplaza\Blog\Model\ResourceModel\Tag\Collection as TagCollection;
use Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\Topic\Collection as TopicCollection;
use Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory as TopicCollectionFactory;
use Mageplaza\Blog\Model\Tag;
use Mageplaza\Blog\Model\TagFactory;
use Mageplaza\Blog\Model\Topic;
use Mageplaza\Blog\Model\TopicFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TermCreatorTest extends TestCase
{
    /**
     * @var TagFactory|MockObject
     */
    private $tagFactory;

    /**
     * @var TopicFactory|MockObject
     */
    private $topicFactory;

    /**
     * @var TagCollectionFactory|MockObject
     */
    private $tagCollectionFactory;

    /**
     * @var TopicCollectionFactory|MockObject
     */
    private $topicCollectionFactory;

    /**
     * @var TermCreator
     */
    private $creator;

    protected function setUp(): void
    {
        $this->tagFactory = $this->createMock(TagFactory::class);
        $this->topicFactory = $this->createMock(TopicFactory::class);
        $this->tagCollectionFactory = $this->createMock(TagCollectionFactory::class);
        $this->topicCollectionFactory = $this->createMock(TopicCollectionFactory::class);
        $this->creator = new TermCreator(
            $this->tagFactory,
            $this->topicFactory,
            $this->tagCollectionFactory,
            $this->topicCollectionFactory
        );
    }

    private function collection(string $class, array $items): MockObject
    {
        $collection = $this->createMock($class);
        $collection->method('addFieldToFilter')->willReturnSelf();
        $collection->method('getItems')->willReturn($items);

        return $collection;
    }

    public function testReturnsExistingCaseInsensitive(): void
    {
        $this->tagCollectionFactory->method('create')->willReturn($this->collection(
            TagCollection::class,
            [new DataObject(['id' => 7, 'name' => 'Sun Hat'])]
        ));
        $this->tagFactory->expects($this->never())->method('create');

        $this->assertSame(['id' => 7, 'name' => 'Sun Hat'], $this->creator->createOrFind('tag', ' sun hat '));
    }

    public function testCreatesNewTopicWithCleanName(): void
    {
        $this->topicCollectionFactory->method('create')
            ->willReturn($this->collection(TopicCollection::class, []));
        $model = $this->createMock(Topic::class);
        $model->expects($this->once())->method('setData')
            ->with(['name' => 'Beach', 'enabled' => 1, 'store_ids' => 0]);
        $model->expects($this->once())->method('save');
        $model->method('getId')->willReturn('12');
        $this->topicFactory->method('create')->willReturn($model);

        $this->assertSame(['id' => 12, 'name' => 'Beach'], $this->creator->createOrFind('topic', ' <b>Beach</b> '));
    }

    public function testCreatesNewTag(): void
    {
        $this->tagCollectionFactory->method('create')->willReturn($this->collection(TagCollection::class, []));
        $model = $this->createMock(Tag::class);
        $model->expects($this->once())->method('save');
        $model->method('getId')->willReturn(3);
        $this->tagFactory->method('create')->willReturn($model);

        $this->assertSame(3, $this->creator->createOrFind('tag', 'zz')['id']);
    }

    public static function invalidProvider(): array
    {
        return [
            'empty' => ['tag', '  '],
            'tags only' => ['tag', '<i></i>'],
            'too long' => ['topic', str_repeat('a', 256)],
            'bad type' => ['post', 'x'],
        ];
    }

    #[DataProvider('invalidProvider')]
    public function testRejectsInvalid(string $type, string $name): void
    {
        $this->tagFactory->expects($this->never())->method('create');
        $this->topicFactory->expects($this->never())->method('create');
        $this->expectException(LocalizedException::class);

        $this->creator->createOrFind($type, $name);
    }
}
