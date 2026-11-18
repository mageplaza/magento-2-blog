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

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\AiIntegration\Api\AiClientInterface;
use Mageplaza\AiIntegration\Api\ConnectionStatusInterface;
use Mageplaza\AiIntegration\Api\ModelListInterface;
use Mageplaza\AiIntegration\Helper\Data as AiHelper;
use Mageplaza\AiIntegration\Exception\AiException;

class Service
{
    public const XML_PATH_MODEL = 'blog/ai/model';
    public const XML_PATH_LANGUAGE = 'blog/ai/language';
    public const SOURCE = 'blog';

    /**
     * @var AiClientInterface
     */
    private $client;

    /**
     * @var ConnectionStatusInterface
     */
    private $status;

    /**
     * @var ModelListInterface
     */
    private $modelList;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var AiHelper
     */
    private $aiHelper;

    /**
     * @var array
     */
    private $limits;

    /**
     * @param AiClientInterface $client
     * @param ConnectionStatusInterface $status
     * @param ModelListInterface $modelList
     * @param ScopeConfigInterface $scopeConfig
     * @param AiHelper $aiHelper
     * @param array $limits
     */
    public function __construct(
        AiClientInterface $client,
        ConnectionStatusInterface $status,
        ModelListInterface $modelList,
        ScopeConfigInterface $scopeConfig,
        AiHelper $aiHelper,
        array $limits = []
    ) {
        $this->client = $client;
        $this->status = $status;
        $this->modelList = $modelList;
        $this->scopeConfig = $scopeConfig;
        $this->aiHelper = $aiHelper;
        $this->limits = $limits;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->status->isAvailable();
    }

    /**
     * @param string $feature
     * @return array
     */
    public function getLimits(string $feature): array
    {
        return $this->limits[$feature] ?? [];
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getLanguage(int $storeId = 0): string
    {
        return trim((string)$this->scopeConfig->getValue(self::XML_PATH_LANGUAGE, ScopeInterface::SCOPE_STORE, $storeId));
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getModel(int $storeId = 0): string
    {
        $model = trim((string)$this->scopeConfig->getValue(self::XML_PATH_MODEL, ScopeInterface::SCOPE_STORE, $storeId));

        return $model !== '' && !$this->modelList->isCustomModel() ? $model : '';
    }

    /**
     * @param int $storeId
     * @return string
     */
    public function getEffectiveModel(int $storeId = 0): string
    {
        return $this->getModel($storeId) ?: $this->aiHelper->getModel();
    }

    /**
     * @param array $messages
     * @param array|null $schema
     * @param array $options
     * @param int $storeId
     * @return array|string
     * @throws AiException
     */
    public function run(array $messages, ?array $schema = null, array $options = [], int $storeId = 0)
    {
        unset($options['model']);
        $model = $this->getModel($storeId);
        if ($model !== '') {
            $options['model'] = $model;
        }
        $options['source'] = self::SOURCE;
        $options['max_retries'] = 0;

        $result = $this->client->complete($messages, $schema, $options);
        if ($schema === null) {
            return $result->getText();
        }
        $data = $result->getData();
        if ($data === null) {
            throw AiException::create(AiException::INVALID_RESPONSE);
        }

        return $data;
    }

    /**
     * @param AiException $e
     * @return array
     */
    public function error(AiException $e): array
    {
        return ['success' => false, 'type' => $e->getType(), 'message' => $e->getMessage()];
    }
}
