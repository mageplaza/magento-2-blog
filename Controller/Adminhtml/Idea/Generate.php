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

namespace Mageplaza\Blog\Controller\Adminhtml\Idea;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Controller\Adminhtml\Ai\AbstractAi;
use Mageplaza\Blog\Model\Ai\Ideas;
use Mageplaza\Blog\Model\Ai\Service;

class Generate extends AbstractAi
{
    public const ADMIN_RESOURCE = 'Mageplaza_Blog::ai_ideas';
    public const DEFAULT_MAX_TOPICS = 10;

    /**
     * @var Ideas
     */
    private $ideas;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Service $service
     * @param Ideas $ideas
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Service $service,
        Ideas $ideas,
        StoreManagerInterface $storeManager
    ) {
        $this->ideas = $ideas;
        $this->storeManager = $storeManager;

        parent::__construct($context, $resultJsonFactory, $service);
    }

    /**
     * @param RequestInterface $request
     * @return array
     */
    protected function process(RequestInterface $request): array
    {
        $limits = $this->service->getLimits('ideas');
        $storeFilter = !empty($limits['store_filter']);
        $storeId = $storeFilter ? (int)$request->getParam('store_id') : $this->getDefaultStoreId();
        $offset = $storeFilter ? max(0, (int)$request->getParam('offset')) : 0;
        $maxTopics = max(0, (int)($limits['max_topics'] ?? self::DEFAULT_MAX_TOPICS));

        return $this->ideas->generate($storeId, $maxTopics, $offset, (bool)$request->getParam('refresh'));
    }

    /**
     * @return int
     */
    protected function getDefaultStoreId(): int
    {
        $store = $this->storeManager->getDefaultStoreView() ?: $this->storeManager->getStore();

        return (int)$store->getId();
    }
}
