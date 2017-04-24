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

class Post extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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
     * Tag relation model
     *
     * @var string
     */
	public $postTagTable;

    /**
     * Topic relation model
     *
     * @var string
     */
	public $postTopicTable;

    /**
     * Blog Category relation model
     *
     * @var string
     */
	public $postCategoryTable;
	public $helperData;
    /**
     * constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Event\ManagerInterface $eventManager,
		\Mageplaza\Blog\Helper\Data $helperData,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        $this->date         = $date;
        $this->eventManager = $eventManager;
		$this->helperData = $helperData;
        parent::__construct($context);
        $this->postTagTable      = $this->getTable('mageplaza_blog_post_tag');
        $this->postTopicTable    = $this->getTable('mageplaza_blog_post_topic');
        $this->postCategoryTable = $this->getTable('mageplaza_blog_post_category');
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mageplaza_blog_post', 'post_id');
    }

    /**
     * Retrieves Post Name from DB by passed id.
     *
     * @param string $id
     * @return string|bool
     */
    public function getPostNameById($id)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('post_id = :post_id');
        $binds   = ['post_id' => (int)$id];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Mageplaza\Blog\Model\Post $object
     * @return $this
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->date->date());
        }
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
    }

    /**
     * after save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Mageplaza\Blog\Model\Post $object
     * @return $this
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $this->saveTagRelation($object);
        $this->saveTopicRelation($object);
        $this->saveCategoryRelation($object);

        return parent::_afterSave($object);
    }

    /**
     * @param \Mageplaza\Blog\Model\Post $post
     * @return array
     */
    public function getTagsPosition(\Mageplaza\Blog\Model\Post $post)
    {
        $select = $this->getConnection()->select()->from(
            $this->postTagTable,
            ['tag_id', 'position']
        )
            ->where(
                'post_id = :post_id'
            );
        $bind   = ['post_id' => (int)$post->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * @param \Mageplaza\Blog\Model\Post $post
     * @return $this
     */
	public function saveTagRelation(\Mageplaza\Blog\Model\Post $post)
    {
        $post->setIsChangedTagList(false);
        $id   = $post->getId();
        $tags = $post->getTagsData();
        if ($tags === null) {
            return $this;
        }
        $oldTags = $post->getTagsPosition();
        $insert  = array_diff_key($tags, $oldTags);
        $delete  = array_diff_key($oldTags, $tags);
        $update  = array_intersect_key($tags, $oldTags);
        $_update = [];
        foreach ($update as $key => $settings) {
            if (isset($oldTags[$key]) && $oldTags[$key] != $settings['position']) {
                $_update[$key] = $settings;
            }
        }
        $update  = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['tag_id IN(?)' => array_keys($delete), 'post_id=?' => $id];
            $adapter->delete($this->postTagTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $tagId => $position) {
                $data[] = [
                    'post_id'  => (int)$id,
                    'tag_id'   => (int)$tagId,
                    'position' => (int)$position['position']
                ];
            }
            $adapter->insertMultiple($this->postTagTable, $data);
        }
        if (!empty($update)) {
            foreach ($update as $tagId => $position) {
                $where = ['post_id = ?' => (int)$id, 'tag_id = ?' => (int)$tagId];
                $bind  = ['position' => (int)$position['position']];
                $adapter->update($this->postTagTable, $bind, $where);
            }
        }
        if (!empty($insert) || !empty($delete)) {
            $tagIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'mageplaza_blog_post_change_tags',
                ['post' => $post, 'tag_ids' => $tagIds]
            );
        }
        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $post->setIsChangedTagList(true);
            $tagIds = array_keys($insert + $delete + $update);
            $post->setAffectedTagIds($tagIds);
        }

        return $this;
    }

    /**
     * @param \Mageplaza\Blog\Model\Post $post
     * @return array
     */
    public function getTopicsPosition(\Mageplaza\Blog\Model\Post $post)
    {
        $select = $this->getConnection()->select()->from(
            $this->postTopicTable,
            ['topic_id', 'position']
        )
            ->where(
                'post_id = :post_id'
            );
        $bind   = ['post_id' => (int)$post->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * @param \Mageplaza\Blog\Model\Post $post
     * @return $this
     */
	public function saveTopicRelation(\Mageplaza\Blog\Model\Post $post)
    {
        $post->setIsChangedTopicList(false);
        $id     = $post->getId();
        $topics = $post->getTopicsData();
        if ($topics === null) {
            return $this;
        }
        $oldTopics = $post->getTopicsPosition();
        $insert    = array_diff_key($topics, $oldTopics);
        $delete    = array_diff_key($oldTopics, $topics);
        $update    = array_intersect_key($topics, $oldTopics);
        $_update   = [];
        foreach ($update as $key => $settings) {
            if (isset($oldTopics[$key]) && $oldTopics[$key] != $settings['position']) {
                $_update[$key] = $settings;
            }
        }
        $update  = $_update;
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['topic_id IN(?)' => array_keys($delete), 'post_id=?' => $id];
            $adapter->delete($this->postTopicTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $topicId => $position) {
                $data[] = [
                    'post_id'  => (int)$id,
                    'topic_id' => (int)$topicId,
                    'position' => (int)$position['position']
                ];
            }
            $adapter->insertMultiple($this->postTopicTable, $data);
        }
        if (!empty($update)) {
            foreach ($update as $topicId => $position) {
                $where = ['post_id = ?' => (int)$id, 'topic_id = ?' => (int)$topicId];
                $bind  = ['position' => (int)$position['position']];
                $adapter->update($this->postTopicTable, $bind, $where);
            }
        }
        if (!empty($insert) || !empty($delete)) {
            $topicIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'mageplaza_blog_post_change_topics',
                ['post' => $post, 'topic_ids' => $topicIds]
            );
        }
        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $post->setIsChangedTopicList(true);
            $topicIds = array_keys($insert + $delete + $update);
            $post->setAffectedTopicIds($topicIds);
        }

        return $this;
    }

    /**
     * @param \Mageplaza\Blog\Model\Post $post
     * @return array
     */
	public function saveCategoryRelation(\Mageplaza\Blog\Model\Post $post)
    {
        $post->setIsChangedCategoryList(false);
        $id         = $post->getId();
        $categories = $post->getCategoriesIds();
        if ($categories === null) {
            return $this;
        }
        $oldCategoryIds = $post->getCategoryIds();
        $insert         = array_diff($categories, $oldCategoryIds);
        $delete         = array_diff($oldCategoryIds, $categories);
        $adapter        = $this->getConnection();

        //\Zend_Debug::dump($delete);die();

        if (!empty($delete)) {
            $condition = ['category_id IN(?)' => $delete, 'post_id=?' => $id];
            $adapter->delete($this->postCategoryTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $categoryId) {
                $data[] = [
                    'post_id'     => (int)$id,
                    'category_id' => (int)$categoryId,
                    'position'    => 1
                ];
            }
            $adapter->insertMultiple($this->postCategoryTable, $data);
        }
        if (!empty($insert) || !empty($delete)) {
            $categoryIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'mageplaza_blog_post_change_categories',
                ['post' => $post, 'category_ids' => $categoryIds]
            );
        }
        if (!empty($insert) || !empty($delete)) {
            $post->setIsChangedCategoryList(true);
            $categoryIds = array_keys($insert + $delete);
            $post->setAffectedCategoryIds($categoryIds);
        }

        return $this;
    }

    /**
     * @param \Mageplaza\Blog\Model\Post $post
     * @return array
     */
    public function getCategoryIds(\Mageplaza\Blog\Model\Post $post)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()->from(
            $this->postCategoryTable,
            'category_id'
        )
            ->where(
                'post_id = ?',
                (int)$post->getId()
            );

        return $adapter->fetchCol($select);
    }

    /**
     * @param \Mageplaza\Blog\Model\Post $post
     * @return array
     */
    public function getTopicIds(\Mageplaza\Blog\Model\Post $post)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()->from(
            $this->postTopicTable,
            'topic_id'
        )
            ->where(
                'post_id = ?',
                (int)$post->getId()
            );

        return $adapter->fetchCol($select);
    }

    public function checkUrlKey($url, $id = null)
    {
        $adapter = $this->getConnection();
        if ($id) {
            $select            = $adapter->select()
                ->from($this->getMainTable(), '*')
                ->where('url_key = :url_key')
                ->where('post_id != :post_id');
            $binds['url_key']  = (string)$url;
            $binds ['post_id'] = (int)$id;
        } else {
            $select = $adapter->select()
                ->from($this->getMainTable(), '*')
                ->where('url_key = :url_key');
            $binds  = ['url_key' => (string)$url];
        }
        $result = $adapter->fetchOne($select, $binds);

        return $result;
    }
	public function generateUrlKey($name, $count){
    	$result = $this->helperData->generateUrlKey($name,$count);
    	return $result;
	}
}