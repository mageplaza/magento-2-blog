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

use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Helper\Data;

/**
 * Class Tag
 * @package Mageplaza\Blog\Model\ResourceModel
 */
class Tag extends AbstractDb
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

    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    public $helperData;

    /**
     * Tag constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Mageplaza\Blog\Helper\Data $helperData
     */
    public function __construct(
        Context $context,
        ManagerInterface $eventManager,
        DateTime $date,
        Data $helperData
    )
    {
        $this->helperData   = $helperData;
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
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('tag_id = :tag_id');
        $binds   = ['tag_id' => (int)$id];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * @inheritdoc
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
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
     * @inheritdoc
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
        $select = $this->getConnection()->select()
            ->from($this->tagPostTable, ['post_id', 'position'])
            ->where('tag_id = :tag_id');

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
        $id    = $tag->getId();
        $posts = $tag->getPostsData();
        if ($posts === null) {
            return $this;
        }
        $oldPosts = $tag->getPostsPosition();
        $insert   = array_diff_key($posts, $oldPosts);
        $delete   = array_diff_key($oldPosts, $posts);
        $update   = array_intersect_key($posts, $oldPosts);
        $_update  = [];
        foreach ($update as $key => $settings) {
            if (isset($oldPosts[$key]) && $oldPosts[$key] != $settings['position']) {
                $_update[$key] = $settings;
            }
        }
        $update  = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['post_id IN(?)' => array_keys($delete), 'tag_id=?' => $id];
            $adapter->delete($this->tagPostTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $postId => $position) {
                $data[] = [
                    'tag_id'   => (int)$id,
                    'post_id'  => (int)$postId,
                    'position' => (int)$position['position']
                ];
            }
            $adapter->insertMultiple($this->tagPostTable, $data);
        }
        if (!empty($update)) {
            foreach ($update as $postId => $position) {
                $where = ['tag_id = ?' => (int)$id, 'post_id = ?' => (int)$postId];
                $bind  = ['position' => (int)$position['position']];
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
}
