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
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Author
 * @package Mageplaza\Blog\Model\ResourceModel
 */
class Author extends AbstractDb
{
    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    public $helperData;

    /**
     * @inheritdoc
     */
    protected $_isPkAutoIncrement = false;

    /**
     * Author constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Mageplaza\Blog\Helper\Data $helperData
     */
    public function __construct(
        Context $context,
        Data $helperData
    )
    {
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('mageplaza_blog_author', 'user_id');
    }

    /**
     * @inheritdoc
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUrlKey(
            $this->helperData->generateUrlKey($this, $object, $object->getUrlKey() ?: $object->getName())
        );

        if (!$object->isObjectNew()) {
            $object->setUpdatedAt(\Zend_Date::now());
        }

        return $this;
    }
}
