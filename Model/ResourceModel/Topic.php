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

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Topic
 * @package Mageplaza\Blog\Model\ResourceModel
 */
class Topic extends AbstractDb
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
    public $topicPostTable;

    /**
     * @var Data
     */
    public $helperData;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * Topic constructor.
     *
     * @param Context $context
     * @param DateTime $date
     * @param ManagerInterface $eventManager
     * @param RequestInterface $request
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        DateTime $date,
        ManagerInterface $eventManager,
        RequestInterface $request,
        Data $helperData
    ) {
        $this->helperData = $helperData;
        $this->date = $date;
        $this->eventManager = $eventManager;
        $this->request = $request;

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
     * @param $id
     *
     * @return string
     * @throws LocalizedException
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
     * @inheritdoc
     */
    protected function _beforeSave(AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }

        if (is_array($object->getStoreIds())) {
            $object->setStoreIds(implode(',', $object->getStoreIds()));
        }

        $object->setUrlKey(
            $this->helperData->generateUrlKey($this, $object, $object->getUrlKey() ?: $object->getName())
        );

        return parent::_beforeSave($object);
    }

    /**
     * @param AbstractModel $object
     *
     * @return AbstractDb
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->savePostRelation($object);

        return parent::_afterSave($object);
    }

    /**
     * @param \Mageplaza\Blog\Model\Topic $topic
     *
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
     *
     * @return $this
     */
    protected function savePostRelation(\Mageplaza\Blog\Model\Topic $topic)
    {
        $topic->setIsChangedPostList(false);
        $id = $topic->getId();
        $posts = $topic->getPostsData();
        $oldPosts = $topic->getPostsPosition();
        if (is_array($posts)) {
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
        }
        $adapter = $this->getConnection();
        if ($posts === null && $this->request->getActionName() === 'save') {
            foreach (array_keys($oldPosts) as $value) {
                $condition = ['post_id =?' => (int)$value, 'topic_id=?' => (int)$id];
                $adapter->delete($this->topicPostTable, $condition);
            }

            return $this;
        }
        if (!empty($delete)) {
            foreach (array_keys($delete) as $value) {
                $condition = ['post_id =?' => (int)$value, 'topic_id=?' => (int)$id];
                $adapter->delete($this->topicPostTable, $condition);
            }
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

    /**
     * Check is import topic
     *
     * @param $importSource
     * @param $oldId
     *
     * @return string
     * @throws LocalizedException
     */
    public function isImported($importSource, $oldId)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable(), 'topic_id')
            ->where('import_source = :import_source');
        $binds = ['import_source' => $importSource . '-' . $oldId];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * @param $importType
     *
     * @throws LocalizedException
     */
    public function deleteImportItems($importType)
    {
        $adapter = $this->getConnection();
        $adapter->delete($this->getMainTable(), "`import_source` LIKE '" . $importType . "%'");
    }
}
