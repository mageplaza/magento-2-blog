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
namespace Mageplaza\Blog\Model\ResourceModel;

class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
	public $date;

    /**
     * Event Manager
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
	public $eventManager;

    /**
     * Post relation model
     *
     * @var string
     */
	public $categoryPostTable;
	public $translitUrl;
    /**
     * constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
		\Magento\Framework\Filter\TranslitUrl $translitUrl,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
    	$this->translitUrl=$translitUrl;
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
     * @param string $id
     * @return string|bool
     */
    public function getCategoryNameById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('category_id = :category_id');
        $binds = ['category_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }
    /**
     * before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Mageplaza\Blog\Model\Category $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }
        /** @var \Mageplaza\Blog\Model\Category $object */
        parent::_beforeSave($object);
        if (!$object->getChildrenCount()) {
            $object->setChildrenCount(0);
        }

        if ($object->isObjectNew()) {
            if ($object->getPosition() === null) {
                $object->setPosition($this->getMaxPosition($object->getPath()) + 1);
            }
            $path = explode('/', $object->getPath());
            $level = count($path)  - ($object->getId() ? 1 : 0);
            $toUpdateChild = array_diff($path, [$object->getId()]);

            if (!$object->hasPosition()) {
                $object->setPosition($this->getMaxPosition(implode('/', $toUpdateChild)) + 1);
            }
            if (!$object->hasLevel()) {
                $object->setLevel($level);
            }
            if (!$object->hasParentId() && $level && !$object->getInitialSetupFlag()) {
                $object->setParentId($path[$level - 1]);
            }
            if (!$object->getId() && !$object->getInitialSetupFlag()) {
                $object->setPath($object->getPath() . '/');
            }
            if (!$object->getInitialSetupFlag()) {
                $this->getConnection()->update(
                    $this->getMainTable(),
                    ['children_count' => 'children_count+1'],
                    ['category_id IN(?)' => $toUpdateChild]
                );
            }
        }
        //Check Url Key
        if ($object->isObjectNew()) {
            $count   = 0;
            $objName = $object->getName();
            if ($object->getUrlKey()) {
                $urlKey = $object->getUrlKey();
            } else {
                $urlKey = $this->generateUrlKey($objName, $count);
            }
            while ($this->checkUrlKey($urlKey)) {
                $count++;
                $urlKey = $this->generateUrlKey($urlKey, $count);
            }
            $object->setUrlKey($urlKey);
        } else {
            $objectId = $object->getId();
            $count    = 0;
            $objName  = $object->getName();
            if ($object->getUrlKey()) {
                $urlKey = $object->getUrlKey();
            } else {
                $urlKey = $this->generateUrlKey($objName, $count);
            }
            while ($this->checkUrlKey($urlKey, $objectId)) {
                $count++;
                $urlKey = $this->generateUrlKey($urlKey, $count);
            }

            $object->setUrlKey($urlKey);
        }
        return $this;
    }
    /**
     * after save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Mageplaza\Blog\Model\Category $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        /** @var \Mageplaza\Blog\Model\Category $object */
        if (substr($object->getPath(), -1) == '/') {
            $object->setPath($object->getPath() . $object->getId());
            $this->savePath($object);
        }
        $this->savePostRelation($object);
        return parent::_afterSave($object);
    }

    /**
     * @param $path
     * @return int|string
     */
    protected function getMaxPosition($path)
    {
        $adapter = $this->getConnection();
        $positionField = $adapter->quoteIdentifier('position');
        $level = count(explode('/', $path));
        $bind = ['c_level' => $level, 'c_path' => $path . '/%'];
        $select = $adapter->select()->from(
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
     * Update path field
     *
     * @param \Mageplaza\Blog\Model\Category $object
     * @return $this
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
     * @return $this
     */
    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_beforeDelete($object);

        /**
         * Update children count for all parent Categories
         */
        $parentIds = $object->getParentIds();
        if ($parentIds) {
            $childDecrease = $object->getChildrenCount() + 1;
            // +1 is itself
            $data = ['children_count' => 'children_count - ' . $childDecrease];
            $where = ['category_id IN(?)' => $parentIds];
            $this->getConnection()->update($this->getMainTable(), $data, $where);
        }
        $this->deleteChildren($object);
        return $this;
    }

    /**
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function deleteChildren(\Magento\Framework\DataObject $object)
    {
        $adapter = $this->getConnection();
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
     * @param \Mageplaza\Blog\Model\Category $category
     * @param \Mageplaza\Blog\Model\Category $newParent
     * @param null $afterCategoryId
     * @return $this
     */
    public function changeParent(
        \Mageplaza\Blog\Model\Category $category,
        \Mageplaza\Blog\Model\Category $newParent,
        $afterCategoryId = null
    ) {
        $childrenCount = $this->getChildrenCount($category->getId()) + 1;
        $table = $this->getMainTable();
        $adapter = $this->getConnection();
        $levelFiled = $adapter->quoteIdentifier('level');
        $pathField = $adapter->quoteIdentifier('path');

        /**
         * Decrease children count for all old Blog Category parent Categories
         */
        $adapter->update(
            $table,
            ['children_count' => 'children_count - ' . $childrenCount],
            ['category_id IN(?)' => $category->getParentIds()]
        );

        /**
         * Increase children count for new Blog Category parents
         */
        $adapter->update(
            $table,
            ['children_count' => 'children_count + ' . $childrenCount],
            ['category_id IN(?)' => $newParent->getPathIds()]
        );

        $position = $this->processPositions($category, $newParent, $afterCategoryId);

        $newPath = sprintf('%s/%s', $newParent->getPath(), $category->getId());
        $newLevel = $newParent->getLevel() + 1;
        $levelDisposition = $newLevel - $category->getLevel();

        /**
         * Update children nodes path
         */
        $adapter->update(
            $table,
            [
                'path' => 'REPLACE(' . $pathField . ',' . $adapter->quote(
                        	$category->getPath() . '/'
                    		) . ', ' . $adapter->quote(
                        	$newPath . '/'
                    		) . ')',
                'level' => $levelFiled . ' + ' . $levelDisposition
            ],
            [$pathField . ' LIKE ?' => $category->getPath() . '/%']
        );
        /**
         * Update moved Blog Category data
         */
        $data = [
            'path' => $newPath,
            'level' => $newLevel,
            'position' => $position,
            'parent_id' => $newParent->getId(),
        ];
        $adapter->update($table, $data, ['category_id = ?' => $category->getId()]);

        // Update Blog Category object to new data
        $category->addData($data);
        $category->unsetData('path_ids');

        return $this;
    }

    /**
     * @param \Mageplaza\Blog\Model\Category $category
     * @param \Mageplaza\Blog\Model\Category $newParent
     * @param $afterCategoryId
     * @return int
     */
    public function processPositions(
        \Mageplaza\Blog\Model\Category $category,
        \Mageplaza\Blog\Model\Category $newParent,
        $afterCategoryId
    ) {
    
        $table = $this->getMainTable();
        $adapter = $this->getConnection();
        $positionField = $adapter->quoteIdentifier('position');

        $bind = ['position' => $positionField . ' - 1'];
        $where = [
            'parent_id = ?' => $category->getParentId(),
            $positionField . ' > ?' => $category->getPosition(),
        ];
        $adapter->update($table, $bind, $where);

        /**
         * Prepare position value
         */
        if ($afterCategoryId) {
            $select = $adapter->select()->from($table, 'position')->where('category_id = :category_id');
            $position = $adapter->fetchOne($select, ['category_id' => $afterCategoryId]);
            $position++;
        } else {
            $position = 1;
        }

        $bind = ['position' => $positionField . ' + 1'];
        $where = ['parent_id = ?' => $newParent->getId(), $positionField . ' >= ?' => $position];
        $adapter->update($table, $bind, $where);

        return $position;
    }

    /**
     * @param $categoryId
     * @return string
     */
    public function getChildrenCount($categoryId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            'children_count'
        )->where(
            'category_id = :category_id'
        );
        $bind = ['category_id' => $categoryId];

        return $this->getConnection()->fetchOne($select, $bind);
    }
    /**
     * @param \Mageplaza\Blog\Model\Category $category
     * @return array
     */
    public function getPostsPosition(\Mageplaza\Blog\Model\Category $category)
    {
        $select = $this->getConnection()->select()->from(
            $this->categoryPostTable,
            ['post_id', 'position']
        )
        ->where(
            'category_id = :category_id'
        );
        $bind = ['category_id' => (int)$category->getId()];
        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * @param \Mageplaza\Blog\Model\Category $category
     * @return $this
     */
    public function savePostRelation(\Mageplaza\Blog\Model\Category $category)
    {
        $category->setIsChangedPostList(false);
        $id = $category->getId();
        $posts = $category->getPostsData();
        if ($posts === null) {
            return $this;
        }
        $oldPosts = $category->getPostsPosition();
        $insert = array_diff_key($posts, $oldPosts);
        $delete = array_diff_key($oldPosts, $posts);
        $update = array_intersect_key($posts, $oldPosts);
        $_update = [];
        foreach ($update as $key => $position) {
            if (isset($oldPosts[$key]) && $oldPosts[$key] != $position) {
                $_update[$key] = $position;
            }
        }
        $update = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['post_id IN(?)' => array_keys($delete), 'category_id=?' => $id];
            $adapter->delete($this->categoryPostTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $postId => $position) {
                $data[] = [
                    'category_id' => (int)$id,
                    'post_id' => (int)$postId,
                    'position' => (int)$position
                ];
            }
            $adapter->insertMultiple($this->categoryPostTable, $data);
        }
        if (!empty($update)) {
            foreach ($update as $postId => $position) {
                $where = ['category_id = ?' => (int)$id, 'post_id = ?' => (int)$postId];
                $bind = ['position' => (int)$position];
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
    public function generateUrlKey($name, $count)
    {
		$text = $this->translitUrl->filter($name);
		if ($count == 0) {
			$count = '';
		}
		if (empty($text)) {
			return 'n-a' . $count;
		}
		return $text . $count;
    }

    public function checkUrlKey($url, $id = null)
    {
        $adapter = $this->getConnection();
        if ($id) {
            $select            = $adapter->select()
                ->from($this->getMainTable(), '*')
                ->where('url_key = :url_key')
                ->where('category_id != :category_id');
            $binds['url_key']  = (string)$url;
            $binds ['category_id'] = (int)$id;
        } else {
            $select = $adapter->select()
                ->from($this->getMainTable(), '*')
                ->where('url_key = :url_key');
            $binds  = ['url_key' => (string)$url];
        }
        $result = $adapter->fetchOne($select, $binds);

        return $result;
    }
}
