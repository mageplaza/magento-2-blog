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

namespace Mageplaza\Blog\Model\Config\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Mageplaza\AiIntegration\Api\ModelListInterface;

class AiModel implements OptionSourceInterface
{
    public const XML_PATH_PROVIDER = 'mp_ai_integration/connection/provider';

    /**
     * @var ModelListInterface
     */
    private $modelList;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ModelListInterface $modelList
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(ModelListInterface $modelList, ScopeConfigInterface $scopeConfig)
    {
        $this->modelList = $modelList;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [['value' => '', 'label' => __('Use AI Integration default')]];
        foreach ($this->modelList->getModels((string)$this->scopeConfig->getValue(self::XML_PATH_PROVIDER)) as $model) {
            $options[] = ['value' => $model, 'label' => $model];
        }

        return $options;
    }
}
