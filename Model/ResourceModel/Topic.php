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

class Topic extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
	public $topicPostTable;
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
        $this->topicPostTable = $this->getTable('mageplaza_blog_post_topic');
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_blog_topic', 'topic_id');
    }

    /**
     * Retrieves Topic Name from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    public function getTopicNameById($id)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('topic_id = :topic_id');
        $binds = ['topic_id' => (int)$id];
        return $adapter->fetchOne($select, $binds);
    }
    /**
     * before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Mageplaza\Blog\Model\Topic $object
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
     * @param \Magento\Framework\Model\AbstractModel|\Mageplaza\Blog\Model\Topic $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->savePostRelation($object);
        return parent::_afterSave($object);
    }

    /**
     * @param \Mageplaza\Blog\Model\Topic $topic
     * @return array
     */
    public function getPostsPosition(\Mageplaza\Blog\Model\Topic $topic)
    {
        $select = $this->getConnection()->select()->from(
            $this->topicPostTable,
            ['post_id', 'position']
        )
        ->where(
            'topic_id = :topic_id'
        );
        $bind = ['topic_id' => (int)$topic->getId()];
        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * @param \Mageplaza\Blog\Model\Topic $topic
     * @return $this
     */
    protected function savePostRelation(\Mageplaza\Blog\Model\Topic $topic)
    {
        $topic->setIsChangedPostList(false);
        $id = $topic->getId();
        $posts = $topic->getPostsData();
        if ($posts === null) {
            return $this;
        }
        $oldPosts = $topic->getPostsPosition();
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
            $condition = ['post_id IN(?)' => array_keys($delete), 'topic_id=?' => $id];
            $adapter->delete($this->topicPostTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $postId => $position) {
                $data[] = [
                    'topic_id' => (int)$id,
                    'post_id' => (int)$postId,
                    'position' => (int)$position['position']
                ];
            }
            $adapter->insertMultiple($this->topicPostTable, $data);
        }
        if (!empty($update)) {
            foreach ($update as $postId => $position) {
                $where = ['topic_id = ?' => (int)$id, 'post_id = ?' => (int)$postId];
                $bind = ['position' => (int)$position['position']];
                $adapter->update($this->topicPostTable, $bind, $where);
            }
        }
        if (!empty($insert) || !empty($delete)) {
            $postIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'mageplaza_blog_topic_change_posts',
                ['topic' => $topic, 'post_ids' => $postIds]
            );
        }
        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $topic->setIsChangedPostList(true);
            $postIds = array_keys($insert + $delete + $update);
            $topic->setAffectedPostIds($postIds);
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
                ->where('topic_id != :topic_id');
            $binds['url_key']  = (string)$url;
            $binds ['topic_id'] = (int)$id;
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
