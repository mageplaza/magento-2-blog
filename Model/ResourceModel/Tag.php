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

class Tag extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
	public $tagPostTable;
	public $helperData;
    /**
     * constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
		\Mageplaza\Blog\Helper\Data $helperData,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
		$this->helperData = $helperData;
        $this->date         = $date;
        $this->eventManager = $eventManager;
        parent::__construct($context);
        $this->tagPostTable = $this->getTable('mageplaza_blog_post_tag');
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_blog_tag', 'tag_id');
    }

    /**
     * Retrieves Tag Name from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    public function getTagNameById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('tag_id = :tag_id');
        $binds = ['tag_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }
    /**
     * before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Mageplaza\Blog\Model\Tag $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
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
        return parent::_beforeSave($object);
    }
    /**
     * after save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Mageplaza\Blog\Model\Tag $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->savePostRelation($object);
        return parent::_afterSave($object);
    }

    /**
     * @param \Mageplaza\Blog\Model\Tag $tag
     * @return array
     */
    public function getPostsPosition(\Mageplaza\Blog\Model\Tag $tag)
    {
        $select = $this->getConnection()->select()->from(
            $this->tagPostTable,
            ['post_id', 'position']
        )
        ->where(
            'tag_id = :tag_id'
        );
        $bind = ['tag_id' => (int)$tag->getId()];
        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * @param \Mageplaza\Blog\Model\Tag $tag
     * @return $this
     */
    protected function savePostRelation(\Mageplaza\Blog\Model\Tag $tag)
    {
        $tag->setIsChangedPostList(false);
        $id = $tag->getId();
        $posts = $tag->getPostsData();
        if ($posts === null) {
            return $this;
        }
        $oldPosts = $tag->getPostsPosition();
        $insert = array_diff_key($posts, $oldPosts);
        $delete = array_diff_key($oldPosts, $posts);
        $update = array_intersect_key($posts, $oldPosts);
        $_update = [];
        foreach ($update as $key => $settings) {
            if (isset($oldPosts[$key]) && $oldPosts[$key] != $settings['position']) {
                $_update[$key] = $settings;
            }
        }
        $update = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['post_id IN(?)' => array_keys($delete), 'tag_id=?' => $id];
            $adapter->delete($this->tagPostTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $postId => $position) {
                $data[] = [
                    'tag_id' => (int)$id,
                    'post_id' => (int)$postId,
                    'position' => (int)$position['position']
                ];
            }
            $adapter->insertMultiple($this->tagPostTable, $data);
        }
        if (!empty($update)) {
            foreach ($update as $postId => $position) {
                $where = ['tag_id = ?' => (int)$id, 'post_id = ?' => (int)$postId];
                $bind = ['position' => (int)$position['position']];
                $adapter->update($this->tagPostTable, $bind, $where);
            }
        }
        if (!empty($insert) || !empty($delete)) {
            $postIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'mageplaza_blog_tag_change_posts',
                ['tag' => $tag, 'post_ids' => $postIds]
            );
        }
        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $tag->setIsChangedPostList(true);
            $postIds = array_keys($insert + $delete + $update);
            $tag->setAffectedPostIds($postIds);
        }
        return $this;
    }
    public function generateUrlKey($name, $count)
    {
		return $this->helperData->generateUrlKey($name,$count);
    }

    public function checkUrlKey($url, $id = null)
    {
        $adapter = $this->getConnection();
        if ($id) {
            $select            = $adapter->select()
                ->from($this->getMainTable(), '*')
                ->where('url_key = :url_key')
                ->where('tag_id != :tag_id');
            $binds['url_key']  = (string)$url;
            $binds ['tag_id'] = (int)$id;
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
