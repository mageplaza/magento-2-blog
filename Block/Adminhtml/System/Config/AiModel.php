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

namespace Mageplaza\Blog\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Mageplaza\AiIntegration\Api\ModelListInterface;

class AiModel extends Field
{
    public const XML_PATH_CUSTOM_MODEL = 'mp_ai_integration/connection/custom_model';

    /**
     * @var ModelListInterface
     */
    private $modelList;

    /**
     * @param Context $context
     * @param ModelListInterface $modelList
     * @param array $data
     */
    public function __construct(Context $context, ModelListInterface $modelList, array $data = [])
    {
        $this->modelList = $modelList;

        parent::__construct($context, $data);
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        if ($this->modelList->isCustomModel()) {
            $id = trim((string)$this->_scopeConfig->getValue(self::XML_PATH_CUSTOM_MODEL));
            $element->setDisabled(true)
                ->setValue('')
                ->setValues([['value' => '', 'label' => __('%1 (Custom Model ID)', $id)]])
                ->setComment(__(
                    'AI Integration uses a custom model ID. Change it in '
                    . 'Stores > Configuration > Mageplaza Extensions > AI Integration.'
                ));

            return parent::_getElementHtml($element);
        }
        $value = (string)$element->getValue();
        $values = (array)$element->getValues();
        if ($value !== '' && !in_array($value, array_column($values, 'value'), true)) {
            $values[] = ['value' => $value, 'label' => __('%1 (not in current list)', $value)];
            $element->setValues($values);
        }

        return parent::_getElementHtml($element);
    }
}
