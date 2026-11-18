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

namespace Mageplaza\Blog\Model\Ai;

use Magento\Framework\Exception\LocalizedException;
use Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory as TopicCollectionFactory;
use Mageplaza\Blog\Model\TagFactory;
use Mageplaza\Blog\Model\TopicFactory;

class TermCreator
{
    public const TYPE_TAG = 'tag';
    public const TYPE_TOPIC = 'topic';
    public const MAX_LENGTH = 255;

    /**
     * @var TagFactory
     */
    private $tagFactory;

    /**
     * @var TopicFactory
     */
    private $topicFactory;

    /**
     * @var TagCollectionFactory
     */
    private $tagCollectionFactory;

    /**
     * @var TopicCollectionFactory
     */
    private $topicCollectionFactory;

    /**
     * @param TagFactory $tagFactory
     * @param TopicFactory $topicFactory
     * @param TagCollectionFactory $tagCollectionFactory
     * @param TopicCollectionFactory $topicCollectionFactory
     */
    public function __construct(
        TagFactory $tagFactory,
        TopicFactory $topicFactory,
        TagCollectionFactory $tagCollectionFactory,
        TopicCollectionFactory $topicCollectionFactory
    ) {
        $this->tagFactory = $tagFactory;
        $this->topicFactory = $topicFactory;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->topicCollectionFactory = $topicCollectionFactory;
    }

    /**
     * @param string $type
     * @param string $name
     * @return array
     * @throws LocalizedException
     */
    public function createOrFind(string $type, string $name): array
    {
        if (!in_array($type, [self::TYPE_TAG, self::TYPE_TOPIC], true)) {
            throw new LocalizedException(__('Invalid request.'));
        }
        $name = $this->clean($name);
        $isTag = $type === self::TYPE_TAG;

        $collection = ($isTag ? $this->tagCollectionFactory : $this->topicCollectionFactory)->create();
        $collection->addFieldToFilter('name', $name);
        foreach ($collection->getItems() as $item) {
            if (mb_strtolower((string)$item->getName(), 'UTF-8') === mb_strtolower($name, 'UTF-8')) {
                return ['id' => (int)$item->getId(), 'name' => (string)$item->getName()];
            }
        }

        $model = ($isTag ? $this->tagFactory : $this->topicFactory)->create();
        $model->setData(['name' => $name, 'enabled' => 1, 'store_ids' => 0]);
        $model->save();

        return ['id' => (int)$model->getId(), 'name' => $name];
    }

    /**
     * @param string $name
     * @return string
     * @throws LocalizedException
     */
    private function clean(string $name): string
    {
        $name = trim(strip_tags($name));
        if ($name === '') {
            throw new LocalizedException(__('Name is required.'));
        }
        if (mb_strlen($name, 'UTF-8') > self::MAX_LENGTH) {
            throw new LocalizedException(__('Name is too long.'));
        }

        return $name;
    }
}
