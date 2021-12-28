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

namespace Mageplaza\Blog\Block\Adminhtml\Widget;

use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Number
 * @package Mageplaza\Blog\Block\Adminhtml\Widget
 */
class Number extends Column
{
    /**
     * @param AbstractElement $element
     *
     * @return AbstractElement
     */
    public function prepareElementHtml(AbstractElement $element)
    {
        $html = '<input type="text" name="' . $element->getName() . '" id="' . $element->getId()
            . '" class=" input-text admin__control-text required-entry _required validate-digits" value="'
            . $element->getValue() . '">';
        $element->setData('value', '');
        $element->setData('after_element_html', $html);

        return $element;
    }
}
