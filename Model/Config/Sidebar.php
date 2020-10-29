<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
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

namespace Mageplaza\Blog\Model\Config;

use Magento\Framework\DataObject;
use Mageplaza\Blog\Api\Data\Config\SidebarInterface;

/**
 * Class Sidebar
 * @package Mageplaza\Blog\Model\Config
 */
class Sidebar extends DataObject implements SidebarInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNumberRecent()
    {
        return $this->getData(self::NUMBER_RECENT);
    }

    /**
     * {@inheritdoc}
     */
    public function setNumberRecent($value)
    {
        $this->setData(self::NUMBER_RECENT, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumberMostView()
    {
        return $this->getData(self::NUMBER_MOST_VIEW);
    }

    /**
     * {@inheritdoc}
     */
    public function setNumberMostView($value)
    {
        $this->setData(self::NUMBER_MOST_VIEW, $value);

        return $this;
    }
}
