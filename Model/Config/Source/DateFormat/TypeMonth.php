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
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Blog\Model\Config\Source\DateFormat;

class TypeMonth implements \Magento\Framework\Option\ArrayInterface
{

    /**
     * Get config TimeZone ( general/locale/timezone )
     * @return mixed
     */
    public function getTimezone()
    {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $context = $om->get('\Magento\Framework\View\Element\Template\Context');
        $storeModel = $context->getStoreManager()->getStore()->getId();
        $timeZone       = $context->getScopeConfig()->getValue(
            'general/locale/timezone',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeModel
        );
        return $timeZone;
    }

    /**
     * Set Datetime Option Array
     * @return array
     */
    public function setOptionArray()
    {
        $dateArray = [
            'F , Y',
            'Y - m',
            'm / Y',
            'M  Y'
        ];
        $result = [];
        for ($i = 0; $i < 4; $i++) {
            $result[$i] = __($dateArray[$i].' ('.date($dateArray[$i], time()).')');
        }

        return $result ;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        date_default_timezone_set($this->getTimezone());
        return $this->setOptionArray();
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        date_default_timezone_set($this->getTimezone());
        return $this->setOptionArray();
    }
}
