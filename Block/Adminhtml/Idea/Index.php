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

namespace Mageplaza\Blog\Block\Adminhtml\Idea;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Widget\Button;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Block\Adminhtml\Post\Edit\AiAssist;
use Mageplaza\Blog\Controller\Adminhtml\Idea\Generate;
use Mageplaza\Blog\Model\Ai\Ideas;
use Mageplaza\Blog\Model\Ai\Service;

class Index extends Template
{
    public const PRO_URL = 'https://www.mageplaza.com/magento-2-blog-extension/';

    /**
     * @var Ideas
     */
    private $ideas;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var array
     */
    private $terms;

    /**
     * @param Context $context
     * @param Ideas $ideas
     * @param StoreManagerInterface $storeManager
     * @param Service $service
     * @param array $data
     */
    public function __construct(
        Context $context,
        Ideas $ideas,
        StoreManagerInterface $storeManager,
        Service $service,
        array $data = []
    ) {
        $this->service = $service;
        $this->ideas = $ideas;
        $this->storeManager = $storeManager;

        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $toolbar = $this->getLayout()->getBlock('page.actions.toolbar');
        if ($toolbar) {
            $enabled = $this->isAvailable() && $this->getTerms()['enough'];
            $buttons = [
                'mpblog_ideas_refresh' => [
                    'id' => 'mpblog-ideas-refresh',
                    'label' => __('Run again'),
                    'class' => 'mpblog-btn-light',
                    'title' => __('Calls your AI provider and uses tokens'),
                    'style' => 'display:none'
                ],
                'mpblog_ideas_generate' => [
                    'id' => 'mpblog-ideas-generate',
                    'label' => __('Generate ideas'),
                    'class' => 'primary',
                    'title' => __('Calls your AI provider and uses tokens'),
                    'disabled' => !$enabled
                ]
            ];
            foreach ($buttons as $name => $data) {
                if ($enabled || $name === 'mpblog_ideas_generate') {
                    $toolbar->setChild($name, $this->getLayout()->createBlock(Button::class, $name, ['data' => $data]));
                }
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * @return int
     */
    public function getMaxTopics(): int
    {
        $limits = $this->service->getLimits('ideas');

        return max(0, (int)($limits['max_topics'] ?? Generate::DEFAULT_MAX_TOPICS));
    }

    /**
     * @return array
     */
    public function getTerms(): array
    {
        $store = $this->storeManager->getDefaultStoreView() ?: $this->storeManager->getStore();

        if ($this->terms === null) {
            $this->terms = $this->ideas->loadTerms((int)$store->getId());
        }

        return $this->terms;
    }

    /**
     * @return string
     */
    public function getNotEnoughMessage(): string
    {
        return $this->ideas->getNotEnoughMessage();
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->service->isAvailable();
    }

    /**
     * @return string
     */
    public function getConfigUrl(): string
    {
        return $this->getUrl(AiAssist::AI_CONFIG_SECTION);
    }

    /**
     * @return string
     */
    public function getJsConfig(): string
    {
        $limits = $this->service->getLimits('ideas');

        return (string)json_encode([
            'Mageplaza_Blog/js/ai/ideas' => [
                'url' => $this->getUrl('mageplaza_blog/idea/generate'),
                'writeButton' => !empty($limits['write_button']),
                'maxTopics' => $this->getMaxTopics(),
                'proUrl' => self::PRO_URL
            ]
        ]);
    }
}
