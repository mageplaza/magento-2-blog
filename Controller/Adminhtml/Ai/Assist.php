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

namespace Mageplaza\Blog\Controller\Adminhtml\Ai;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Mageplaza\Blog\Model\Ai\Assist as AssistModel;
use Mageplaza\Blog\Model\Ai\PostStore;
use Mageplaza\Blog\Model\Ai\Service;
use Mageplaza\Blog\Model\ResourceModel\Tag\CollectionFactory as TagCollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\Topic\CollectionFactory as TopicCollectionFactory;

class Assist extends AbstractAi
{
    public const ADMIN_RESOURCE = 'Mageplaza_Blog::ai_assist';
    public const MAX_TERMS = 300;

    /**
     * @var AssistModel
     */
    private $assist;

    /**
     * @var PostStore
     */
    private $postStore;

    /**
     * @var TagCollectionFactory
     */
    private $tagCollectionFactory;

    /**
     * @var TopicCollectionFactory
     */
    private $topicCollectionFactory;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Service $service
     * @param AssistModel $assist
     * @param PostStore $postStore
     * @param TagCollectionFactory $tagCollectionFactory
     * @param TopicCollectionFactory $topicCollectionFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Service $service,
        AssistModel $assist,
        PostStore $postStore,
        TagCollectionFactory $tagCollectionFactory,
        TopicCollectionFactory $topicCollectionFactory
    ) {
        $this->assist = $assist;
        $this->postStore = $postStore;
        $this->tagCollectionFactory = $tagCollectionFactory;
        $this->topicCollectionFactory = $topicCollectionFactory;

        parent::__construct($context, $resultJsonFactory, $service);
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    protected function process(RequestInterface $request): array
    {
        $type = (string)$request->getParam('type');
        $storeId = $this->postStore->getStoreId($request->getParam('store_ids', ''));
        $tags = [];
        $topics = [];
        if ($type === 'taxonomy') {
            if ($this->_authorization->isAllowed('Mageplaza_Blog::tag')) {
                $tags = $this->terms($this->tagCollectionFactory->create(), $storeId);
            }
            if ($this->_authorization->isAllowed('Mageplaza_Blog::topic')) {
                $topics = $this->terms($this->topicCollectionFactory->create(), $storeId);
            }
        }

        return $this->assist->generate(
            $type,
            (string)$request->getParam('name'),
            (string)$request->getParam('content'),
            $tags,
            $topics,
            $storeId
        );
    }

    /**
     * @param mixed $collection
     * @param int $storeId
     * @return array
     */
    private function terms($collection, int $storeId): array
    {
        $collection->addFieldToFilter('store_ids', [['finset' => 0], ['finset' => $storeId]])
            ->setPageSize(self::MAX_TERMS);
        $rows = [];
        foreach ($collection->getItems() as $item) {
            $rows[] = ['id' => (int)$item->getId(), 'name' => (string)$item->getName()];
        }

        return $rows;
    }
}
