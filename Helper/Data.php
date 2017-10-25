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

namespace Mageplaza\Blog\Helper;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\TranslitUrl;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Model\AuthorFactory;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\TagFactory;
use Mageplaza\Blog\Model\TopicFactory;
use Mageplaza\Core\Helper\AbstractData as CoreHelper;

/**
 * Class Data
 * @package Mageplaza\Blog\Helper
 */
class Data extends CoreHelper
{
    const TYPE_POST = 'post';
    const TYPE_CATEGORY = 'category';
    const TYPE_TAG = 'tag';
    const TYPE_TOPIC = 'topic';
    const TYPE_AUTHOR = 'author';
    const TYPE_MONTHLY = 'month';

    /**
     * @var \Mageplaza\Blog\Model\PostFactory
     */
    public $postFactory;

    /**
     * @var \Mageplaza\Blog\Model\CategoryFactory
     */
    public $categoryFactory;

    /**
     * @var \Mageplaza\Blog\Model\TagFactory
     */
    public $tagFactory;

    /**
     * @var \Mageplaza\Blog\Model\TopicFactory
     */
    public $topicFactory;

    /**
     * @var \Mageplaza\Blog\Model\AuthorFactory
     */
    public $authorFactory;

    /**
     * @var \Magento\Framework\Filter\TranslitUrl
     */
    public $translitUrl;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Mageplaza\Blog\Model\PostFactory $postFactory
     * @param \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
     * @param \Mageplaza\Blog\Model\TagFactory $tagFactory
     * @param \Mageplaza\Blog\Model\TopicFactory $topicFactory
     * @param \Mageplaza\Blog\Model\AuthorFactory $authorFactory
     * @param \Magento\Framework\Filter\TranslitUrl $translitUrl
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        PostFactory $postFactory,
        CategoryFactory $categoryFactory,
        TagFactory $tagFactory,
        TopicFactory $topicFactory,
        AuthorFactory $authorFactory,
        TranslitUrl $translitUrl,
        DateTime $dateTime
    )
    {
        $this->postFactory     = $postFactory;
        $this->categoryFactory = $categoryFactory;
        $this->tagFactory      = $tagFactory;
        $this->topicFactory    = $topicFactory;
        $this->authorFactory   = $authorFactory;
        $this->translitUrl     = $translitUrl;
        $this->dateTime        = $dateTime;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * Is enable module on frontend
     *
     * @param null $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        $isModuleOutputEnabled = $this->isModuleOutputEnabled();

        return $isModuleOutputEnabled && $this->getBlogConfig('general/enabled', $store);
    }

    /**
     * @return \Mageplaza\Blog\Helper\Image
     */
    public function getImageHelper()
    {
        return $this->objectManager->get(Image::class);
    }

    /**
     * @param $code
     * @param null $storeId
     * @return mixed
     */
    public function getBlogConfig($code, $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';

        return $this->getConfigValue('blog' . $code, $storeId);
    }

    /**
     * @param $code
     * @param null $storeId
     * @return mixed
     */
    public function getSeoConfig($code, $storeId = null)
    {
        return $this->getBlogConfig('seo/' . $code, $storeId);
    }

    /**
     * @return mixed
     */
    public function showAuthorInfo()
    {
        return $this->getBlogConfig('general/display_author');
    }

    /**
     * @param null $store
     * @return string
     */
    public function getBlogName($store = null)
    {
        return $this->getBlogConfig('general/name', $store) ?: 'Blog';
    }

    /**
     * @param null $store
     * @return string
     */
    public function getRoute($store = null)
    {
        return $this->getBlogConfig('general/url_prefix', $store) ?: 'blog';
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getUrlSuffix($store = null)
    {
        return $this->getBlogConfig('general/url_suffix', $store) ?: '';
    }

    /**
     * @param null $type
     * @param null $id
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getPostCollection($type = null, $id = null, $storeId = null)
    {
        if (is_null($id)) {
            $id = $this->_request->getParam('id');
        }

        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->getPostList();

        switch ($type) {
            case self::TYPE_AUTHOR:
                $collection->addFieldToFilter('author_id', $id);
                break;
            case self::TYPE_CATEGORY:
                $collection->join(
                    ['category' => $collection->getTable('mageplaza_blog_post_category')],
                    'main_table.post_id=category.post_id AND category.category_id=' . $id,
                    ['position']
                );
                break;
            case self::TYPE_TAG:
                $collection->join(
                    ['tag' => $collection->getTable('mageplaza_blog_post_tag')],
                    'main_table.post_id=tag.post_id AND tag.tag_id=' . $id,
                    ['position']
                );
                break;
            case self::TYPE_TOPIC:
                $collection->join(
                    ['topic' => $collection->getTable('mageplaza_blog_post_topic')],
                    'main_table.post_id=topic.post_id AND topic.topic_id=' . $id,
                    ['position']
                );
                break;
            case self::TYPE_MONTHLY:
                $collection->addFieldToFilter('publish_date', ['like' => $id . '%']);
                break;
            default:
                break;
        }

        return $collection;
    }

    /**
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getPostList($storeId = null)
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->getObjectList(self::TYPE_POST, $storeId)
            ->addFieldToFilter('publish_date', ["to" => $this->dateTime->date()])
            ->setOrder('publish_date', 'desc');

        return $collection;
    }

    /**
     * get category collection
     * @param $array
     * @return array|string
     */
    public function getCategoryCollection($array)
    {
        $collection = $this->getObjectList(self::TYPE_CATEGORY)
            ->addFieldToFilter('category_id', ['in' => $array]);

        return $collection;
    }

    /**
     * Get object collection (Category, Tag, Post, Topic)
     *
     * @param null $type
     * @param null $storeId
     * @return \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getObjectList($type = null, $storeId = null)
    {
        $collection = $this->getFactoryByType($type)
            ->create()
            ->getCollection()
            ->addFieldToFilter('enabled', 1);

        $this->addStoreFilter($collection, $storeId);

        return $collection;
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection $collection
     * @param null $storeId
     * @return mixed
     */
    public function addStoreFilter($collection, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $collection->addFieldToFilter('store_ids', [
            ['finset' => Store::DEFAULT_STORE_ID],
            ['finset' => $storeId]
        ]);

        return $collection;
    }

    /**
     * @param $post
     * @param bool $modify
     * @return \Mageplaza\Blog\Model\Author
     */
    public function getAuthorByPost($post, $modify = false)
    {
        $author = $this->authorFactory->create();

        $authorId = $modify ? $post->getModifierId() : $post->getAuthorId();
        if ($authorId) {
            $author->load($authorId);
        }

        return $author;
    }

    /**
     * @param null $urlKey
     * @param null $type
     * @return string
     */
    public function getBlogUrl($urlKey = null, $type = null)
    {
        if (is_object($urlKey)) {
            $urlKey = $urlKey->getUrlKey();
        }

        $urlKey = ($type ? $type . '/' : '') . $urlKey;
        $url    = $this->_getUrl($this->getRoute() . '/' . $urlKey);

        return rtrim($url, '/') . $this->getUrlSuffix();
    }

    /**
     * @param $value
     * @param null $code
     * @param null $type
     * @return \Mageplaza\Blog\Model\Author|\Mageplaza\Blog\Model\Category|\Mageplaza\Blog\Model\Post|\Mageplaza\Blog\Model\Tag|\Mageplaza\Blog\Model\Topic
     */
    public function getObjectByParam($value, $code = null, $type = null)
    {
        $object = $this->getFactoryByType($type)
            ->create()
            ->load($value, $code);

        return $object;
    }

    /**
     * @param $type
     * @return \Mageplaza\Blog\Model\AuthorFactory|\Mageplaza\Blog\Model\CategoryFactory|\Mageplaza\Blog\Model\PostFactory|\Mageplaza\Blog\Model\TagFactory|\Mageplaza\Blog\Model\TopicFactory
     */
    public function getFactoryByType($type = null)
    {
        switch ($type) {
            case self::TYPE_CATEGORY:
                $object = $this->categoryFactory;
                break;
            case self::TYPE_TAG:
                $object = $this->tagFactory;
                break;
            case self::TYPE_AUTHOR:
                $object = $this->authorFactory;
                break;
            case self::TYPE_TOPIC:
                $object = $this->topicFactory;
                break;
            default:
                $object = $this->postFactory;
        }

        return $object;
    }

    /**
     * Generate url_key for post, tag, topic, category, author
     *
     * @param $resource
     * @param $object
     * @param $name
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateUrlKey($resource, $object, $name)
    {
        $attempt = -1;
        do {
            if ($attempt++ >= 10) {
                throw new LocalizedException(__('Unable to generate url key. Please check the setting and try again.'));
            }

            $urlKey = $this->translitUrl->filter($name);
            if ($urlKey) {
                $urlKey = $urlKey . ($attempt ?: '');
            }
        } while ($this->checkUrlKey($resource, $object, $urlKey));

        return $urlKey;
    }

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     * @param $object
     * @param $urlKey
     * @return bool
     */
    public function checkUrlKey($resource, $object, $urlKey)
    {
        if (empty($urlKey)) {
            return true;
        }

        $adapter = $resource->getConnection();
        $select  = $adapter->select()
            ->from($resource->getMainTable(), '*')
            ->where('url_key = :url_key');

        $binds = ['url_key' => (string)$urlKey];

        if ($id = $object->getId()) {
            $select->where($resource->getIdFieldName() . ' != :object_id');
            $binds['object_id'] = (int)$id;
        }

        $result = $adapter->fetchOne($select, $binds);

        return $result;
    }

    /**
     * get date formatted
     * @param $date
     * @param bool $monthly
     * @return false|string
     */
    public function getDateFormat($date, $monthly = false)
    {
        $dateTime = (new \DateTime($date, new \DateTimeZone('UTC')));
        $dateTime->setTimezone(new \DateTimeZone($this->getTimezone()));

        $dateType   = $this->getBlogConfig($monthly ? 'monthly_archive/date_type_monthly' : 'general/date_type');
        $dateFormat = $dateTime->format($dateType);

        return $dateFormat;
    }

    /**
     * get configuration zone
     * @return mixed
     */
    public function getTimezone()
    {
        return $this->getConfigValue('general/locale/timezone');
    }
}
