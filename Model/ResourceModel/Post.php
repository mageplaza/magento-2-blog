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

use Magento\Backend\Model\Auth;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\Author;
use Mageplaza\Blog\Model\AuthorFactory;
use Mageplaza\Blog\Model\Post as PostModel;

/**
 * Class Post
 * @package Mageplaza\Blog\Model\ResourceModel
 */
class Post extends AbstractDb
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

    /**
     * @var string
     */
    public $postProductTable;

    /**
     * @var Data
     */
    public $helperData;

    /**
     * @var AuthorFactory
     */
    protected $_authorFactory;

    /**
     * @var Auth
     */
    protected $_auth;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var string
     */
    protected $postTrafficTable;

    /**
     * @var string
     */
    protected $postAuthorTable;

    /**
     * Post constructor.
     *
     * @param Context $context
     * @param DateTime $date
     * @param ManagerInterface $eventManager
     * @param Auth $auth
     * @param Data $helperData
     * @param RequestInterface $request
     * @param AuthorFactory $authorFactory
     */
    public function __construct(
        Context $context,
        DateTime $date,
        ManagerInterface $eventManager,
        Auth $auth,
        Data $helperData,
        RequestInterface $request,
        AuthorFactory $authorFactory
    ) {
        $this->date           = $date;
        $this->eventManager   = $eventManager;
        $this->_auth          = $auth;
        $this->helperData     = $helperData;
        $this->_request       = $request;
        $this->_authorFactory = $authorFactory;

        parent::__construct($context);

        $this->postTagTable      = $this->getTable('mageplaza_blog_post_tag');
        $this->postTopicTable    = $this->getTable('mageplaza_blog_post_topic');
        $this->postCategoryTable = $this->getTable('mageplaza_blog_post_category');
        $this->postProductTable  = $this->getTable('mageplaza_blog_post_product');
        $this->postTrafficTable  = $this->getTable('mageplaza_blog_post_traffic');
        $this->postAuthorTable   = $this->getTable('mageplaza_blog_author');
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
     * @param int $id
     *
     * @return string
     * @throws LocalizedException
     */
    public function getPostNameById($id)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'name')
            ->where('post_id = :post_id');
        $binds   = ['post_id' => (int) $id];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * before save callback
     *
     * @param AbstractModel $object
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _beforeSave(AbstractModel $object)
    {
        if (is_array($object->getStoreIds())) {
            $object->setStoreIds(implode(',', $object->getStoreIds()));
        }

        $object->setUrlKey(
            $this->helperData->generateUrlKey($this, $object, $object->getUrlKey() ?: $object->getName())
        );

        return $this;
    }

    /**
     * @param PostModel|AbstractModel $object
     * @return AbstractDb
     * @throws LocalizedException
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->saveTagRelation($object);
        $this->saveTopicRelation($object);
        $this->saveCategoryRelation($object);
        $this->saveProductRelation($object);

        if ($this->_request->getActionName() !== 'manage') {
            $this->saveAuthor();
        }

        return parent::_afterSave($object);
    }

    /**
     * @param PostModel $post
     *
     * @return $this
     * @throws LocalizedException
     */
    public function saveTagRelation(PostModel $post)
    {
        $post->setIsChangedTagList(false);
        $id      = $post->getId();
        $tags    = $post->getTagsIds();
        $oldTags = $post->getTagIds();

        if ($tags === null) {
            return $this;
        }

        $insert  = array_diff($tags, $oldTags);
        $delete  = array_diff($oldTags, $tags);
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['tag_id IN(?)' => $delete, 'post_id=?' => $id];
            $adapter->delete($this->postTagTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $tagId) {
                $data[] = [
                    'post_id'  => (int) $id,
                    'tag_id'   => (int) $tagId,
                    'position' => 1
                ];
            }
            $adapter->insertMultiple($this->postTagTable, $data);
        }
        if (!empty($insert) || !empty($delete)) {
            $tagIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'mageplaza_blog_post_change_tags',
                ['post' => $post, 'tag_ids' => $tagIds]
            );
        }

        if (!empty($insert) || !empty($delete)) {
            $post->setIsChangedTagList(true);
            $tagIds = array_keys($insert + $delete);
            $post->setAffectedTagIds($tagIds);
        }

        return $this;
    }

    /**
     * @param PostModel $post
     *
     * @return $this
     * @throws LocalizedException
     */
    public function saveTopicRelation(PostModel $post)
    {
        $post->setIsChangedTopicList(false);
        $id        = $post->getId();
        $topics    = $post->getTopicsIds();
        $oldTopics = $post->getTopicIds();

        if ($topics === null) {
            return $this;
        }

        $insert  = array_diff($topics, $oldTopics);
        $delete  = array_diff($oldTopics, $topics);
        $adapter = $this->getConnection();
        if (!empty($delete)) {
            $condition = ['topic_id IN(?)' => $delete, 'post_id=?' => $id];
            $adapter->delete($this->postTopicTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $topicId) {
                $data[] = [
                    'post_id'  => (int) $id,
                    'topic_id' => (int) $topicId,
                    'position' => 1
                ];
            }
            $adapter->insertMultiple($this->postTopicTable, $data);
        }

        if (!empty($insert) || !empty($delete)) {
            $topicIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'mageplaza_blog_post_change_topics',
                ['post' => $post, 'topic_ids' => $topicIds]
            );
        }
        if (!empty($insert) || !empty($delete)) {
            $post->setIsChangedTopicList(true);
            $topicIds = array_keys($insert + $delete);
            $post->setAffectedTopicIds($topicIds);
        }

        return $this;
    }

    /**
     * @param PostModel $post
     *
     * @return $this
     * @throws LocalizedException
     */
    public function saveCategoryRelation(PostModel $post)
    {
        $post->setIsChangedCategoryList(false);
        $id             = $post->getId();
        $categories     = $post->getCategoriesIds();
        $oldCategoryIds = $post->getCategoryIds();

        if ($categories === null) {
            return $this;
        }

        $insert         = array_diff($categories, $oldCategoryIds);
        $delete         = array_diff($oldCategoryIds, $categories);
        $adapter        = $this->getConnection();

        if (!empty($delete)) {
            $condition = ['category_id IN(?)' => $delete, 'post_id=?' => $id];
            $adapter->delete($this->postCategoryTable, $condition);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $categoryId) {
                $data[] = [
                    'post_id'     => (int) $id,
                    'category_id' => (int) $categoryId,
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
     * @param PostModel $post
     *
     * @return array
     */
    public function getCategoryIds(PostModel $post)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()->from(
            $this->postCategoryTable,
            'category_id'
        )
            ->where(
                'post_id = ?',
                (int) $post->getId()
            );

        return $adapter->fetchCol($select);
    }

    /**
     * @param PostModel $post
     *
     * @return array
     */
    public function getTagIds(PostModel $post)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()->from(
            $this->postTagTable,
            'tag_id'
        )
            ->where(
                'post_id = ?',
                (int) $post->getId()
            );

        return $adapter->fetchCol($select);
    }

    /**
     * @param PostModel $post
     *
     * @return array
     */
    public function getTopicIds(PostModel $post)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()->from($this->postTopicTable, 'topic_id')
            ->where('post_id = ?', (int) $post->getId());

        return $adapter->fetchCol($select);
    }

    /**
     * @param PostModel $post
     * @return array
     */
    public function getAuthor(PostModel $post)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()->from($this->postAuthorTable, '*')
            ->where('user_id = ?', (int) $post->getAuthorId());

        return $adapter->fetchRow($select);
    }

    /**
     * @param PostModel $post
     *
     * @return array
     */
    public function getViewTraffic(PostModel $post)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()->from($this->postTrafficTable, 'numbers_view')
            ->where('post_id = ?', (int) $post->getId());

        return $adapter->fetchCol($select);
    }

    /**
     * @param PostModel $post
     *
     * @return $this
     */
    public function saveProductRelation(PostModel $post)
    {
        $post->setIsChangedProductList(false);
        $id          = $post->getId();
        $products    = $post->getProductsData();
        $oldProducts = $post->getProductsPosition();

        if (is_array($products)) {
            $insert  = array_diff_key($products, $oldProducts);
            $delete  = array_diff_key($oldProducts, $products);
            $update  = array_intersect_key($products, $oldProducts);
            $_update = [];
            foreach ($update as $key => $settings) {
                if (isset($oldProducts[$key]) && $oldProducts[$key] != $settings['position']) {
                    $_update[$key] = $settings;
                }
            }
            $update = $_update;
        }
        $adapter = $this->getConnection();
        if ($products === null && $this->_request->getActionName() === 'save') {
            foreach (array_keys($oldProducts) as $value) {
                $condition = ['entity_id =?' => (int) $value, 'post_id=?' => (int) $id];
                $adapter->delete($this->postProductTable, $condition);
            }

            return $this;
        }
        if (!empty($delete)) {
            foreach (array_keys($delete) as $value) {
                $condition = ['entity_id =?' => (int) $value, 'post_id=?' => (int) $id];
                $adapter->delete($this->postProductTable, $condition);
            }
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $entityId => $position) {
                $data[] = [
                    'post_id'   => (int) $id,
                    'entity_id' => (int) $entityId,
                    'position'  => (int) $position['position']
                ];
            }
            $adapter->insertMultiple($this->postProductTable, $data);
        }
        if (!empty($update)) {
            foreach ($update as $entityId => $position) {
                $where = ['post_id = ?' => (int) $id, 'entity_id = ?' => (int) $entityId];
                $bind  = ['position' => (int) $position['position']];
                $adapter->update($this->postProductTable, $bind, $where);
            }
        }
        if (!empty($insert) || !empty($delete)) {
            $entityIds = array_unique(array_merge(array_keys($insert), array_keys($delete)));
            $this->eventManager->dispatch(
                'mageplaza_blog_post_change_products',
                ['post' => $post, 'entity_ids' => $entityIds]
            );
        }
        if (!empty($insert) || !empty($update) || !empty($delete)) {
            $post->setIsChangedProductList(true);
            $entityIds = array_keys($insert + $delete + $update);
            $post->setAffectedEntityIds($entityIds);
        }

        return $this;
    }

    /**
     * @param PostModel $post
     *
     * @return array
     */
    public function getProductsPosition(PostModel $post)
    {
        $select = $this->getConnection()->select()->from(
            $this->postProductTable,
            ['entity_id', 'position']
        )
            ->where(
                'post_id = :post_id'
            );
        $bind   = ['post_id' => (int) $post->getId()];

        return $this->getConnection()->fetchPairs($select, $bind);
    }

    /**
     * Check post url key is exists
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
            ->from($this->getMainTable(), 'post_id')
            ->where('url_key = :url_key');
        $binds   = ['url_key' => $urlKey];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * Save the post author when creating post
     */
    public function saveAuthor()
    {
        $currentUser = $this->_auth->getUser();

        if ($currentUser) {
            $currentUserId = $currentUser->getId();
            /** @var Author $author */
            $author = $this->_authorFactory->create()->load($currentUserId);

            /** Create the new author if that author isn't exist */
            if (!$author->getId()) {
                $author->setId($currentUserId)
                    ->setName($currentUser->getName())->save();
            }
        }
    }

    /**
     * Check is imported post
     *
     * @param string $importSource
     * @param string $oldId
     *
     * @return string
     * @throws LocalizedException
     */
    public function isImported($importSource, $oldId)
    {
        $adapter = $this->getConnection();
        $select  = $adapter->select()
            ->from($this->getMainTable(), 'post_id')
            ->where('import_source = :import_source');
        $binds   = ['import_source' => $importSource . '-' . $oldId];

        return $adapter->fetchOne($select, $binds);
    }

    /**
     * @param string $importType
     *
     * @return int
     * @throws LocalizedException
     */
    public function deleteImportItems($importType)
    {
        $adapter = $this->getConnection();

        return $adapter->delete($this->getMainTable(), "`import_source` LIKE '" . $importType . "%'");
    }
}
