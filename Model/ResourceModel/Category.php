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

namespace Mageplaza\Blog\Model\ResourceModel;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\Category as CategoryModel;
use Zend_Db_Expr;

/**
 * Class Category
 * @package Mageplaza\Blog\Model\ResourceModel
 */
class Category extends AbstractDb
{
    /**
     * Date model
     *
     * @var DateTime
     */
    public $date;

    /**
     * Event Manager
     *
     * @var ManagerInterface
     */
    public $eventManager;

    /**
     * Post relation model
     *
     * @var string
     */
    public $categoryPostTable;

    /**
     * @var Data
     */
    public $helperData;

    /**
     * Category constructor.
     *
     * @param Data $helperData
     * @param DateTime $date
     * @param ManagerInterface $eventManager
     * @param Context $context
     */
    public function __construct(
        Context $context,
        DateTime $date,
        ManagerInterface $eventManager,
        Data $helperData
    ) {
        $this->helperData   = $helperData;
        $this->date         = $date;
        $this->eventManager = $eventManager;

        parent::__construct($context);

        $this->categoryPostTable = $this->getTable('mageplaza_blog_post_category');
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_blog_category', 'category_id');
    }

    /**
     * Retrieves Blog Category Name from DB by passed id.
     *
     * @param int $id
     *
     * @return string
     * @throws LocalizedException
     */
    public function getCategoryNameById($id)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('category_id = :category_id');
        $binds   = ['category_id' => (int) $id];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * Before save call back
     *
     * @param AbstractModel $object
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }
        /** @var CategoryModel $object */
        parent::_beforeSave($object);

        if (!$object->getChildrenCount()) {
            $object->setChildrenCount(0);
        }

        if ($object->isObjectNew()) {
            if ($object->getPosition() === null) {
                $object->setPosition($this->getMaxPosition($object->getPath()) + 1);
            }
            $path          = explode('/', $object->getPath());
            $level         = count($path) - ($object->getId() ? 1 : 0);
            $toUpdateChild = array_diff($path, [$object->getId()]);

            if (!$object->hasPosition()) {
                $object->setPosition($this->getMaxPosition(implode('/', $toUpdateChild)) + 1);
            }
            if (!$object->hasLevel()) {
                $object->setLevel($level);
            }
            if (!$object->hasParentId() && $level) {
                $object->setParentId($path[$level - 1]);
            }
            if (!$object->getId()) {
                $object->setPath($object->getPath() . '/');
            }

            $this->getConnection()->update(
                $this->getMainTable(),
                ['children_count' => 'children_count+1'],
                ['category_id IN(?)' => $toUpdateChild]
            );
        }

        if (is_array($object->getStoreIds())) {
            $object->setStoreIds(implode(',', $object->getStoreIds()));
        }

        $object->setUrlKey(
            $this->helperData->generateUrlKey($this, $object, $object->getUrlKey() ?: $object->getName())
        );

        return $this;
    }

    /**
     * @param AbstractModel $object
     *
     * @return AbstractDb
     * @throws LocalizedException
     */
    protected function _afterSave(AbstractModel $object)
    {
        /** @var CategoryModel $object */
        if (substr($object->getPath(), -1) === '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->savePath($object);
        }
        $this->savePostRelation($object);

        return parent::_afterSave($object);
    }

    /**
     * @param string $path
     *
     * @return int|string
     */
    protected function getMaxPosition($path)
    {
        $adapter       = $this->getConnection();
        $positionField = $adapter->quoteIdentifier('position');
        $level         = count(explode('/', $path));
        $bind          = ['c_level' => $level, 'c_path' => $path . '/%'];
        $select        = $adapter->select()->from(
            $this->getTable('mageplaza_blog_category'),
            'MAX(' . $positionField . ')'
        )->where(
            $adapter->quoteIdentifier('path') . ' LIKE :c_path'
        )->where(
            $adapter->quoteIdentifier('level') . ' = :c_level'
        );

        $position = $adapter->fetchOne($select, $bind);
        if (!$position) {
            $position = 0;
        }

        return $position;
    }

    /**
     * Check category url key is exists
     *
     * @param string $urlKey
     *
     * @return string
     * @throws LocalizedException
     */
    public function isDuplicateUrlKey($urlKey)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'category_id')
            ->where('url_key = :url_key');
        $binds   = ['url_key' => $urlKey];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * Check is imported category
     *
     * @param string $importSource
     * @param int $oldId
     *
     * @return string
     * @throws LocalizedException
     */
    public function isImported($importSource, $oldId)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'category_id')
            ->where('import_source = :import_source');
        $binds   = ['import_source' => $importSource . '-' . $oldId];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * Update path field
     *
     * @param Object $object
     *
     * @return $this
     * @throws LocalizedException
     */
    public function savePath($object)
    {
        if ($object->getId()) {
            $this->getConnection()->update(
                $this->getMainTable(),
                ['path' => $object->getPath()],
                ['category_id = ?' => $object->getId()]
            );
            $object->unsetData('path_ids');
        }

        return $this;
    }

    /**
     * @param AbstractModel $object
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeDelete(AbstractModel $object)
    {
        parent::_beforeDelete($object);

        /**
         * Update children count for all parent Categories
         */
        $parentIds = $object->getParentIds();
        if ($parentIds) {
            $childDecrease = $object->getChildrenCount() + 1;
            // +1 is itself
            $data  = ['children_count' => 'children_count - ' . $childDecrease];
            $where = ['category_id IN(?)' => $parentIds];
            $this->getConnection()->update($this->getMainTable(), $data, $where);
        }
        $this->deleteChildren($object);

        return $this;
    }

    /**
     * @param DataObject $object
     *
     * @return $this
     * @throws LocalizedException
     */
    public function deleteChildren(DataObject $object)
    {
        $adapter   = $this->getConnection();
        $pathField = $adapter->quoteIdentifier('path');

        $select = $adapter->select()->from(
            $this->getMainTable(),
            ['category_id']
        )->where(
            $pathField . ' LIKE :c_path'
        );

        $childrenIds = $adapter->fetchCol($select, ['c_path' => $object->getPath() . '/%']);

        if (!empty($childrenIds)) {
            $adapter->delete($this->getMainTable(), ['category_id IN (?)' => $childrenIds]);
        }

        /**
         * Add deleted children ids to object
         * This data can be used in after delete event
         */
        $object->setDeletedChildrenIds($childrenIds);

        return $this;
    }

    /**
     * @param CategoryModel $category
     * @param CategoryModel $newParent
     * @param null $afterCategoryId
     *
     * @return $this
     * @throws LocalizedException
     */
    public function changeParent(
        CategoryModel $category,
        CategoryModel $newParent,
        $afterCategoryId = null
    ) {
        $childrenCount = (int) $this->getChildrenCount($category->getId()) + 1;
        $table         = $this->getMainTable();
        $adapter       = $this->getConnection();
        $levelField    = $adapter->quoteIdentifier('level');
        $pathField     = $adapter->quoteIdentifier('path');

        /**
         * Decrease children count for all old Blog Category parent Categories
         */
        $adapter->update(
            $table,
            ['children_count' => new Zend_Db_Expr('children_count - ' . $childrenCount)],
            ['category_id IN(?)' => $category->getParentIds()]
        );

        /**
         * Increase children count for new Blog Category parents
         */
        $adapter->update(
            $table,
            ['children_count' => new Zend_Db_Expr('children_count + ' . $childrenCount)],
            ['category_id IN(?)' => $newParent->getPathIds()]
        );

        $position         = $this->processPositions($category, $newParent, $afterCategoryId);
        $newPath          = sprintf('%s/%s', $newParent->getPath(), $category->getId());
        $newLevel         = $newParent->getLevel() + 1;
        $levelDisposition = $newLevel - $category->getLevel();

        /**
         * Update children nodes path
         */
        $adapter->update(
            $table,
            [
                'path'  => new Zend_Db_Expr(
                    'REPLACE(' . $pathField . ',' . $adapter->quote(
                        $category->getPath() . '/'
                    ) . ', ' . $adapter->quote(
                        $newPath . '/'
                    ) . ')'
                ),
                'level' => new Zend_Db_Expr($levelField . ' + ' . $levelDisposition)
            ],
            [$pathField . ' LIKE ?' => $category->getPath() . '/%']
        );
        /**
         * Update moved Blog Category data
         */
        $data = [
            'path'      => $newPath,
            'level'     => $newLevel,
            'position'  => $position,
            'parent_id' => $newParent->getId(),
        ];
        $adapter->update($table, $data, ['category_id = ?' => $category->getId()]);

        /** Update Blog Category object to new data */
        $category->addData($data);
        $category->unsetData('path_ids');

        return $this;
    }

    /**
     * @param CategoryModel $category
     * @param CategoryModel $newParent
     * @param int|null $afterCategoryId
     *
     * @return int|string
     * @throws LocalizedException
     */
    public function processPositions(
        CategoryModel $category,
        CategoryModel $newParent,
        $afterCategoryId
    ) {
        $table   = $this->getMainTable();
        $connect = $this->getConnection();
        /** Get old category position */
        $positionOld = $category->getPosition();
        /** Get new category position */
        if (empty($afterCategoryId)) {
            $positionNew = 1;
        } else {
            $select      = $connect->select()->from($table, 'position')->where('category_id = :category_id');
            $positionNew = $connect->fetchOne($select, ['category_id' => $afterCategoryId]);
        }

        /** Update position when the item is moved */
        /** Move to other category parent */
        if ($category->getParentId() != $newParent->getId()) {
            if ($afterCategoryId == 0) {
                $positionNew = 0;
            }
            $positionNew++;
            // phpcs:disable Magento2.SQL.RawQuery
            $sql = "UPDATE `" . $table . "` SET `position`= (`position`-1) WHERE `parent_id`= "
                . $category->getParentId() . " AND `position` >= " . $positionOld;
            $connect->query($sql);
            $sql = "UPDATE `" . $table . "` SET `position`= (`position`+1) WHERE `parent_id`= " . $newParent->getId()
                . " AND `position` >= " . $positionNew;
            $connect->query($sql);
        } else {
            /** Move in the same parent */
            /** Move down */
            if ($positionNew > $positionOld) {
                $sql = "UPDATE `" . $table . "` SET `position`= (`position`-1) WHERE `parent_id`= "
                    . $newParent->getId() . " AND `position` <= " . $positionNew;
                $connect->query($sql);
                $sql = "UPDATE `" . $table . "` SET `position`= (`position`+1) WHERE `parent_id`= "
                    . $newParent->getId() . " AND `position` < " . $positionOld;
                $connect->query($sql);
            } else {
                /** Move up */
                $positionNew++;
                if (empty($afterCategoryId)) {
                    $positionNew = 1;
                }
                $sql = "UPDATE `" . $table . "` SET `position`= (`position`+1) WHERE `parent_id`= "
                    . $newParent->getId() . " AND `position` >= " . $positionNew;
                $connect->query($sql);
                $sql = "UPDATE `" . $table . "` SET `position`= (`position`-1) WHERE `parent_id`= "
                    . $newParent->getId() . " AND `position` > " . $positionOld;
                $connect->query($sql);
            }
        }

        return $positionNew;
    }

    /**
     * @param int $categoryId
     *
     * @return string
     * @throws LocalizedException
     */
    public function getChildrenCount($categoryId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            'children_count'
        )->where(
            'category_id = :category_id'
        );
        $bind   = ['category_id' => $categoryId];

        return $this->getConnection()->fetchOne($select, $bind);
    }

    /**
     * @param CategoryModel $category
     *
     * @return array
     */
    public function getPostsPosition(CategoryModel $category)
    {
        $select = $this->getConnection()->select()->from(
            $this->categoryPostTable,
            ['post_id', 'position']
        )
            ->where(
                'category_id = :category_id'
            );
        $bind   = ['category_id' => (int) $category->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * @param CategoryModel $category
     *
     * @return $this
     */
    public function savePostRelation(CategoryModel $category)
    {
        $category->setIsChangedPostList(false);
        $id    = $category->getId();
        $posts = $category->getPostsData();
        if ($posts === null) {
            return $this;
        }
        $oldPosts = $category->getPostsPosition();
        $insert   = array_diff_key($posts, $oldPosts);
        $delete   = array_diff_key($oldPosts, $posts);
        $update   = array_intersect_key($posts, $oldPosts);
        $_update  = [];
        foreach ($update as $key => $position) {
            if (isset($oldPosts[$key]) && $oldPosts[$key] != $position) {
                $_update[$key] = $position;
            }
        }
        $update  = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['post_id IN(?)' => array_keys($delete), 'category_id=?' => $id];
            $adapter->delete($this->categoryPostTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $postId => $position) {
                $data[] = [
                    'category_id' => (int) $id,
                    'post_id'     => (int) $postId,
                    'position'    => (int) $position
                ];
            }
            $adapter->insertMultiple($this->categoryPostTable, $data);
        }
        if (!empty($update)) {
            foreach ($update as $postId => $position) {
                $where = ['category_id = ?' => (int) $id, 'post_id = ?' => (int) $postId];
                $bind  = ['position' => (int) $position];
                $adapter->update($this->categoryPostTable, $bind, $where);
            }
        }
        if (!empty($insert) || !empty($delete)) {
            $postIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'mageplaza_blog_category_change_posts',
                ['category' => $category, 'post_ids' => $postIds]
            );
        }
        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $category->setIsChangedPostList(true);
            $postIds = array_keys($insert + $delete + $update);
            $category->setAffectedPostIds($postIds);
        }

        return $this;
    }

    /**
     * @param string $importType
     *
     * @throws LocalizedException
     */
    public function deleteImportItems($importType)
    {
        $adapter = $this->getConnection();
        $adapter->delete($this->getMainTable(), "`import_source` LIKE '" . $importType . "%'");
    }
}
