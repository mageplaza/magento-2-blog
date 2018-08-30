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
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Comment
 * @package Mageplaza\Blog\Model\ResourceModel
 */
class Comment extends AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_blog_comment', 'comment_id');
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (is_array($object->getStoreIds())) {
            $object->setStoreIds(implode(',', $object->getStoreIds()));
        }

        return $this;
    }

    /**
     * Check is imported category
     *
     * @param $importSource
     * @param $oldId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isImported($importSource, $oldId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'comment_id')
            ->where('import_source = :import_source');
        $binds = ['import_source' => $importSource . '-' . $oldId];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * @param $importType
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteImportItems($importType)
    {
        $adapter = $this->getConnection();
        $adapter->delete($this->getMainTable(), "`import_source` LIKE '" . $importType . "%'");
    }
}
