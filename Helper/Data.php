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

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Model\AuthorFactory;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\Post;
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
    const TYPE_CATEGORY = 'category';
    const TYPE_TAG = 'tag';
    const TYPE_TOPIC = 'topic';
    const TYPE_AUTHOR = 'author';
    const TYPE_MONTHLY = 'month';

    /**
     * @var \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory
     */
    public $postCollectionFactory;

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
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $store;

    /**
     * @var \Mageplaza\Blog\Model\Traffic
     */
    public $modelTraffic;

    /**
     * @var \Mageplaza\Blog\Model\AuthorFactory
     */
    public $authorFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    public $customerSession;

    /**
     * @var \Magento\Customer\Model\Url
     */
    public $loginUrl;

    /**
     * @var \Magento\Framework\Filter\TranslitUrl
     */
    public $translitUrl;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $dateTime;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    public $dateTimeFormat;

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
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Framework\Filter\TranslitUrl $translitUrl
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory
     * @param \Mageplaza\Blog\Model\Traffic $traffic
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
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url,
        \Magento\Framework\Filter\TranslitUrl $translitUrl,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
        \Mageplaza\Blog\Model\Traffic $traffic
    )
    {
        $this->customerSession       = $session;
        $this->loginUrl              = $url;
        $this->postFactory           = $postFactory;
        $this->categoryFactory       = $categoryFactory;
        $this->tagFactory            = $tagFactory;
        $this->topicFactory          = $topicFactory;
        $this->authorFactory         = $authorFactory;
        $this->store                 = $storeManager;
        $this->modelTraffic          = $traffic;
        $this->translitUrl           = $translitUrl;
        $this->dateTime              = $dateTime;
        $this->dateTimeFormat        = $localeDate;
        $this->postCollectionFactory = $postCollectionFactory;

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
     * Get Size Bar Configure
     * @param $code
     * @param null $storeId
     * @return mixed
     */
    public function getSidebarConfig($code, $storeId = null)
    {
        return $this->getBlogConfig('sidebar/' . $code, $storeId);
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
     * get post list by month
     * @param null $type
     * @return mixed
     */
    public function getSelectedPostByMonth($type = null)
    {
        $month = $this->_getRequest()->getParam('month');

        return $list = ($month) ? $type->getSelectedPostsCollection()
            ->addFieldToFilter('publish_date', ['like' => $month . '%'])
            : $type->getSelectedPostsCollection();
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
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getPostList($storeId = null)
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->postFactory->create()
            ->getCollection()
            ->addFieldToFilter('enabled', 1)
            ->addFieldToFilter('publish_date', ["lt" => $this->dateTime->date()])
            ->setOrder('publish_date', 'desc');

        $this->addStoreFilter($collection, $storeId);

        return $collection;
    }

    /**
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Category\Collection
     */
    public function getCategoryList($storeId = null)
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Category\Collection $collection */
        $collection = $this->categoryFactory->create()
            ->getCollection()
            ->addFieldToFilter('enabled', 1);

        $this->addStoreFilter($collection, $storeId);

        return $collection;
    }

    /**
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Tag\Collection
     */
    public function getTagList($storeId = null)
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Tag\Collection $collection */
        $collection = $this->tagFactory->create()
            ->getCollection()
            ->addFieldToFilter('enabled', 1);

        $this->addStoreFilter($collection, $storeId);

        return $collection;
    }

    /**
     * @param null $storeId
     * @return \Mageplaza\Blog\Model\ResourceModel\Topic\Collection
     */
    public function getTopicList($storeId = null)
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Topic\Collection $collection */
        $collection = $this->topicFactory->create()
            ->getCollection()
            ->addFieldToFilter('enabled', 1);

        $this->addStoreFilter($collection, $storeId);

        return $collection;
    }

    /**
     * get category collection
     * @param $array
     * @return array|string
     */
    public function getCategoryCollection($array)
    {
        $collection = $this->getCategoryList()
            ->addFieldToFilter('category_id', ['in' => $array]);

        return $collection;
    }

    /**
     * get url by post
     * @param $post
     * @return string
     */
    public function getUrlByPost($post)
    {
        $urlKey = '';
        if ($post->getUrlKey()) {
            $url_prefix = $this->getRoute();
            $url_suffix = $this->getUrlSuffix();

            if ($url_prefix) {
                $urlKey .= $url_prefix . '/post/';
            }
            $urlKey .= $post->getUrlKey();
            if ($url_suffix) {
                $urlKey .= $url_suffix;
            }
        }

        return $this->_getUrl($urlKey);
    }

    /**
     * @return mixed
     */
    public function showAuthorInfo()
    {
        return $this->getBlogConfig('general/display_author');
    }

    /**
     * @param $post
     * @return \Mageplaza\Blog\Model\Author|null
     */
    public function getAuthorByPost($post)
    {
        if ($post instanceof Post) {
            $authorId = $post->getAuthorId();
            if (!$authorId) {
                return null;
            }
        } else {
            $authorId = $post;
        }

        $author = $this->authorFactory->create()
            ->load($authorId);

        return $author;
    }

    /**
     * get post by id
     * @param $id
     * @return \Mageplaza\Blog\Model\Post | null
     */
    public function getPostById($id)
    {
        $post = $this->postFactory->create()->load($id);

        return $post;
    }

    /**
     * get post by url
     * @param $url
     * @return \Mageplaza\Blog\Model\Post | null
     */
    public function getPostByUrl($url)
    {
        $url   = $this->checkSuffix($url);
        $posts = $this->postFactory->create()->load($url, 'url_key');

        return $posts;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function checkSuffix($url)
    {
        $url_suffix = $this->getUrlSuffix();
        if (strpos($url, $url_suffix) !== false) {
            $url = str_replace($url_suffix, '', $url);
        }

        return $url;
    }

    /**
     * get media url
     * @return mixed
     */
    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
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
     * @param null $urlKey
     * @param null $type
     * @return string
     */
    public function getBlogUrl($urlKey = null, $type = null)
    {
        if (is_object($urlKey)) {
            $urlKey = $urlKey->getUrlKey();
        }

        $urlKey = ($type ? $type . '/' : '') . '/' . $urlKey;
        $url    = $this->_getUrl($this->getRoute() . '/' . $urlKey);

        return rtrim($url, '/') . $this->getUrlSuffix();
    }

    /**
     * get list category html of post
     * @param $post
     * @return null|string
     */
    public function getPostCategoryHtml($post)
    {
        $categories   = $this->getCategoryCollection($post->getCategoryIds());
        $categoryHtml = [];
        if (empty($categories)) {
            return null;
        } else {
            foreach ($categories as $_cat) {
                $categoryHtml[] = '<a class="mp-info" href="' . $this->getCategoryUrl($_cat->getUrlKey()) . '">' . $_cat->getName()
                    . '</a>';
            }
        }
        $result = implode(', ', $categoryHtml);

        return $result;
    }

    /**
     * get category by param
     * @param $code
     * @param $param
     * @return \Mageplaza\Blog\Model\Category | null
     */
    public function getCategoryByParam($code, $param)
    {
        if ($code == 'id') {
            return $this->categoryFactory->create()->load($param);
        } else {
            return $this->categoryFactory->create()->load($param, $code);
        }
    }

    /**
     * get tag by param
     * @param $code
     * @param $param
     * @return \Mageplaza\Blog\Model\Tag | null
     */
    public function getTagByParam($code, $param)
    {
        if ($code == 'id') {
            return $this->tagFactory->create()->load($param);
        } else {
            return $this->tagFactory->create()->load($param, $code);
        }
    }

    /**
     * get author by param
     * @param $code
     * @param $param
     * @return \Mageplaza\Blog\Model\Author | null
     */
    public function getAuthorByParam($code, $param)
    {
        if ($code == 'id') {
            return $this->authorFactory->create()->load($param);
        } else {
            return $this->authorFactory->create()->load($param, $code);
        }
    }

    /**
     * get topic by param
     * @param $code
     * @param $param
     * @return \Mageplaza\Blog\Model\Topic | null
     */
    public function getTopicByParam($code, $param)
    {
        if ($code == 'id') {
            return $this->topicFactory->create()->load($param);
        } else {
            return $this->topicFactory->create()->load($param, $code);
        }
    }

    /**
     * get most view post
     * @return array|string
     */
    public function getMosviewPosts()
    {
        $collection = $this->getPostList();
        $collection->getSelect()
            ->joinLeft(
                ['traffic' => $collection->getTable('mageplaza_blog_post_traffic')],
                'main_table.post_id=traffic.post_id',
                'numbers_view'
            )
            ->order('numbers_view DESC')
            ->limit((int)$this->getBlogConfig('sidebar/number_mostview_posts') ?: 1);

        return $collection;
    }

    /**
     * get recent post
     * @return array|string
     */
    public function getRecentPost()
    {
        $collection = $this->getPostList();
        $collection->getSelect()
            ->limit((int)$this->getBlogConfig('sidebar/number_recent_posts') ?: 1);

        return $collection;
    }

    /**
     * check customer is logged in or not
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * get login url
     * @return string
     */
    public function getLoginUrl()
    {
        return $this->loginUrl->getLoginUrl();
    }

    /**
     * get customer data
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    public function getCustomerData()
    {
        return $this->customerSession->getCustomerData();
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
        $name = $this->strReplace($name);

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
     * replace vietnamese characters to english characters
     * @param $str
     * @return mixed|string
     */
    public function strReplace($str)
    {
        $str = trim(mb_strtolower($str));
        $str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
        $str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
        $str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
        $str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
        $str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
        $str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
        $str = preg_replace('/(đ)/', 'd', $str);

        return $str;
    }

//************************* Monthly Archive widget functions  ***************************

    /**
     * get posts publish_date
     * @return array
     */
    public function getPostDate()
    {
        $posts     = $this->getPostList();
        $postDates = [];
        if ($posts) {
            foreach ($posts as $post) {
                $postDates[] = $post->getPublishDate();
            }
        }

        return $postDates;
    }

    /**
     * get date label
     * @return array
     */
    public function getDateLabel()
    {
        $posts     = $this->getPostList();
        $postDates = [];

        if ($posts) {
            foreach ($posts as $post) {
                $postDates[] = $this->getDateFormat($post->getPublishDate(), true);
            }
        }
        $result = array_values(array_unique($postDates));

        return $result;
    }

    /**
     * get array of posts's date formatted
     * @return array
     */
    public function getDateArray()
    {
        $dateArray = [];
        foreach ($this->getPostDate() as $postDate) {
            $dateArray[] = date("F Y", $this->dateTime->timestamp($postDate));
        }

        return $dateArray;
    }

    /**
     * get count of posts's date
     * @return array
     */
    public function getDateArrayCount()
    {
        return $dateArrayCount = array_values(array_count_values($this->getDateArray()));
    }

    /**
     * @return array
     */
    public function getDateArrayUnique()
    {
        return $dateArrayUnique = array_values(array_unique($this->getDateArray()));
    }

    /**
     * get date count
     * @return int|mixed
     */
    public function getDateCount()
    {
        $limit          = $this->getBlogConfig('monthly_archive/number_records') ?: 5;
        $dateArrayCount = $this->getDateArrayCount();
        $count          = count($dateArrayCount);
        $result         = ($count < $limit) ? $count : $limit;

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

        if ($monthly) {

            $dateType   = $this->getBlogConfig('monthly_archive/date_type_monthly');
            $dateFormat = $dateTime->format($dateType);

            return $dateFormat;
        }

        $dateType   = $this->getBlogConfig('general/date_type');
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

    /**
     * @param $id
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    public function getRelatedPostList($id)
    {
        /** @var \Mageplaza\Blog\Model\ResourceModel\Post\Collection $collection */
        $collection = $this->postFactory->create()->getCollection();
        $collection->getSelect()->join([
            'related' => $collection->getTable('mageplaza_blog_post_product')],
            'related.post_id=main_table.post_id AND related.entity_id=' . $id . ' AND main_table.enabled=1'
        );
        $collection->setOrder('publish_date', 'DESC');

        return $collection;
    }

    /**
     * @return string
     */
    public function getCurrentDate()
    {
        return $this->dateTime->date();
    }
}
