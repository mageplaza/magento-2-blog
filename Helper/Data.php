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
namespace Mageplaza\Blog\Helper;

use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\TagFactory;
use Mageplaza\Blog\Model\TopicFactory;
use Mageplaza\Blog\Model\AuthorFactory;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

class Data extends CoreHelper
{
    const XML_PATH_BLOG = 'blog/';
    const POST_IMG = 'mageplaza/blog/post/image';
    const AUTHOR_IMG = 'mageplaza/blog/author/image';
	const DEFAULT_URL_PREFIX = 'blog';
	const CATEGORY = 'category';
    const TAG = 'tag';
    const TOPIC = 'topic';
    const AUTHOR = 'author';
    const MONTHLY = 'month';

    public $postfactory;
	public $categoryfactory;
	public $tagfactory;
	public $topicfactory;
	public $store;
	public $modelTraffic;
	public $authorfactory;
	public $customerSession;
	public $loginUrl;
	public $translitUrl;
	public $dateTime;

	public function __construct(
		\Magento\Customer\Model\Session $session,
		\Magento\Customer\Model\Url $url,
		Context $context,
        ObjectManagerInterface $objectManager,
        PostFactory $postFactory,
        CategoryFactory $categoryFactory,
        TagFactory $tagFactory,
        TopicFactory $topicFactory,
		AuthorFactory $authorFactory,
        TemplateContext $templateContext,
		\Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
		\Magento\Framework\Filter\TranslitUrl $translitUrl,
		\Mageplaza\Blog\Model\Traffic $traffic
    ) {
    	$this->customerSession = $session;
		$this->loginUrl = $url;
		$this->postfactory     = $postFactory;
        $this->categoryfactory = $categoryFactory;
        $this->tagfactory      = $tagFactory;
        $this->topicfactory    = $topicFactory;
		$this->authorfactory    = $authorFactory;
		$this->dateTime   = $dateTime;
        $this->store = $templateContext->getStoreManager();
        $this->modelTraffic = $traffic;
        $this->translitUrl = $translitUrl;
        parent::__construct($context, $objectManager, $templateContext->getStoreManager());
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

    public function getBlogConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_BLOG . $code, $storeId);
    }

	/**
	 * get sidebar config
	 */
	public function getSidebarConfig($code, $storeId = null)
	{
		return $this->getBlogConfig('sidebar/'.$code, $storeId);
	}

	/**
	 * @param $code, $storeId = null
	 * @return mixed
	 */
	public function getSeoConfig($code, $storeId = null)
	{
		return $this->getBlogConfig('seo/'.$code, $storeId);
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
			->addFieldToFilter('created_at',['like'=>$month . '%'])
			: $type->getSelectedPostsCollection();
	}

	/**
	 * get post list
	 * @param null $type
	 * @param null $id
	 * @return array|string
	 */
    public function getPostList($type = null, $id = null)
    {
        $list          = '';
        $posts         = $this->postfactory->create();
        $categoryModel = $this->categoryfactory->create();
        $tagModel      = $this->tagfactory->create();
        $topicModel    = $this->topicfactory->create();
        if ($type == null) {
			$list = $posts->getCollection();
        } elseif ($type == self::CATEGORY) {
            $category = $categoryModel->load($id);
			$list     = $category->getSelectedPostsCollection();
        } elseif ($type == self::TAG) {
            $tag  = $tagModel->load($id);
			$list = $tag->getSelectedPostsCollection();
        } elseif ($type == self::TOPIC) {
            $topic = $topicModel->load($id);
			$list  = $topic->getSelectedPostsCollection();
        } elseif ($type == self::AUTHOR) {
			$list = $posts->getCollection()->addFieldToFilter('author_id',$id);
		} elseif ($type == self::MONTHLY) {
			$list = $posts->getCollection()->addFieldToFilter('created_at',['like'=>$id . '%']);
		}

        if ($list->getSize()) {
            $list->setOrder('created_at', 'desc')
                ->addFieldToFilter('enabled', 1);

			$results = $this->filterItems($list);
            return $results ? $results : '';
        }

        return '';
    }

	/**
	 * get category list
	 * @return array|string
	 */
    public function getCategoryList()
    {
        $category = $this->categoryfactory->create();
        $list     = $category->getCollection()->addFieldToFilter('enabled', 1);
        $result = $this->filterItems($list);
        if ($result == '') {
            return '';
        }
        return $result;
    }

	/**
	 * get tag list
	 * @return array|string
	 */
    public function getTagList()
    {
        $tag  = $this->tagfactory->create();
        $list = $tag->getCollection()
            ->addFieldToFilter('enabled', 1);
        $result = $this->filterItems($list);
        if ($result == '') {
            return '';
        }
        return $result;
    }

	/**
	 * get topic list
	 * @return array|string
	 */
	public function getTopicList()
	{
		$topic  = $this->topicfactory->create();
		$list = $topic->getCollection()
			->addFieldToFilter('enabled', 1);
		$result = $this->filterItems($list);
		if ($result == '') {
			return '';
		}
		return $result;
	}

	/**
	 * get category collection
	 * @param $array
	 * @return array|string
	 */
    public function getCategoryCollection($array)
    {
        $category = $this->categoryfactory->create();
        $list     = $category->getCollection()
            ->addFieldToFilter('enabled', 1)
            ->addFieldToFilter('category_id', ['in' => $array]);
        $result = $this->filterItems($list);
        if ($result == '') {
            return '';
        }
        return $result;
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
            $url_prefix = $this->getBlogConfig('general/url_prefix') ?: self::DEFAULT_URL_PREFIX;
            $url_suffix = $this->getBlogConfig('general/url_suffix');

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
	 * get author by post'authorId
	 * @param $authorId
	 * @return \Mageplaza\Blog\Model\Author | null
	 */
	public function getAuthorByPost($authorId)
	{
		$author = $this->authorfactory->create();
		$list = $author->load($authorId);
		return $list;
	}

	/**
	 * get blog url
	 * @param $code
	 * @return string
	 */
    public function getBlogUrl($code)
    {
    	$blogUrl = $this->getBlogConfig('general/url_prefix') ?: self::DEFAULT_URL_PREFIX;
        return $this->_getUrl($blogUrl . '/' . $code);
    }

	/**
	 * get post by url
	 * @param $url
	 * @return \Mageplaza\Blog\Model\Post | null
	 */
    public function getPostByUrl($url)
    {
        $url   = $this->checkSuffix($url);
        $posts = $this->postfactory->create()->load($url, 'url_key');
        return $posts;
    }

	/**
	 * @param $url
	 * @return mixed
	 */
    public function checkSuffix($url)
    {
        $url_suffix = $this->getBlogConfig('general/url_suffix');
        if (strpos($url, $url_suffix) !== false) {
            $url = str_replace($url_suffix, '', $url);
        }

        return $url;
    }

//    public function getPostsByTag()
//    {
//        $posts      = $this->postfactory->create();
//        $collection = $posts->getCollection()->addFieldToFilter('enabled', 1);
//        $result = $this->filterItems($collection);
//        if ($result == '') {
//            return '';
//        }
//        return $result;
//    }

//    public function getPostsByCategory()
//    {
//        $collection = true;
//
//        return $collection;
//    }

	/**
	 * get image url by image file name
	 * @param $image
	 * @return string
	 */
    public function getImageUrl($image)
    {
        return $this->getBaseMediaUrl(). self::POST_IMG . $image;
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
	 * get category url
	 * @param $category
	 * @return string
	 */
    public function getCategoryUrl($category)
    {
    	$urlPrefix = $this->getBlogConfig('general/url_prefix') ?: self::DEFAULT_URL_PREFIX;
        return $this->_getUrl($urlPrefix .'/'. self::CATEGORY .'/'. $category->getUrlKey());
    }

	/**
	 * get tag url
	 * @param $tag
	 * @return string
	 */
    public function getTagUrl($tag)
    {
		$urlPrefix = $this->getBlogConfig('general/url_prefix') ?: self::DEFAULT_URL_PREFIX;
        return $this->_getUrl($urlPrefix .'/'. self::TAG .'/'. $tag->getUrlKey());
    }

	/**
	 * get author url
	 * @param $author
	 * @return string
	 */
	public function getAuthorUrl($author)
	{
		$urlPrefix = $this->getBlogConfig('general/url_prefix') ?: self::DEFAULT_URL_PREFIX;
		return $this->_getUrl($urlPrefix .'/'. self::AUTHOR .'/'. $author->getUrlKey());
	}

	/**
	 * get topic url
	 * @param $topic
	 * @return string
	 */
    public function getTopicUrl($topic)
    {
		$urlPrefix = $this->getBlogConfig('general/url_prefix') ?: self::DEFAULT_URL_PREFIX;
		return $this->_getUrl($urlPrefix .'/'. self::TOPIC .'/'. $topic->getUrlKey());
    }

	/**
	 * get monthly archive url
	 * @param $month
	 * @return string
	 */
	public function getMonthlyUrl($month)
	{
		$urlPrefix = $this->getBlogConfig('general/url_prefix') ?: self::DEFAULT_URL_PREFIX;
		return $this->_getUrl($urlPrefix .'/'. self::MONTHLY .'/'. $month);
	}

	/**
	 * get list category html of post
	 * @param $post
	 * @return null|string
	 */
    public function getPostCategoryHtml($post)
    {
        $categories = $this->getCategoryCollection($post->getCategoryIds());
        $categoryHtml = [];
        if (empty($categories)) {
            return null;
        } else {
            foreach ($categories as $_cat) {
                $categoryHtml[] = '<a class="mp-info" href="' . $this->getCategoryUrl($_cat) . '">' . $_cat->getName()
					. '</a>';
            }
        }
        $result = implode(', ', $categoryHtml);

        return $result;
    }

	/**
	 * get post by id
	 * @param $id
	 * @return \Mageplaza\Blog\Model\Post | null
	 */
    public function getPost($id)
    {
        $post = $this->postfactory->create()->load($id);
        return $post;
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
            return $this->categoryfactory->create()->load($param);
        } else {
            return $this->categoryfactory->create()->load($param, $code);
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
            return $this->tagfactory->create()->load($param);
        } else {
            return $this->tagfactory->create()->load($param, $code);
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
			return $this->authorfactory->create()->load($param);
		} else {
			return $this->authorfactory->create()->load($param, $code);
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
            return $this->topicfactory->create()->load($param);
        } else {
            return $this->topicfactory->create()->load($param, $code);
        }
    }


//    public function getCategoryByPost($postId)
//    {
//        $post = $this->postfactory->create()->load($postId);
//        return $post->getSelectedCategoriesCollection();
//    }

//    public function getTagsByPost($postId)
//    {
//        $post = $this->postfactory->create()->load($postId);
//        return $post->getSelectedTagsCollection();
//    }

//    public function getTopicByPost($postId)
//    {
//        $post = $this->postfactory->create()->load($postId);
//        return $post->getSelectedTopicsCollection();
//    }

	/**
	 * get most view post
	 * @return array|string
	 */
    public function getMosviewPosts()
    {
        $posts = $this->modelTraffic->getCollection()->addFieldToFilter('enabled', 1);
        $posts->join(
            'mageplaza_blog_post',
            'main_table.post_id=mageplaza_blog_post.post_id',
            '*'
        );
        $posts->setOrder('numbers_view', 'DESC');
        $limitMostView = $this->getBlogConfig('sidebar/number_mostview_posts') ?: 1;
        $postList = $this->filterItems($posts, $limitMostView);
        if ($postList == '') {
            return '';
        }
        return $postList;
    }

    /**
     * get recent post
	 * @return array|string
     */
    public function getRecentPost()
    {
        $posts = $this->postfactory->create()
            ->getCollection()
            ->addFieldToFilter('enabled', 1)
            ->setOrder('created_at', 'DESC');

        $limitRecent = $this->getBlogConfig('sidebar/number_recent_posts') ?: 1;
        $postList = $this->filterItems($posts, $limitRecent);
        if ($postList == '') {
            return '';
        }
        return $postList;
    }

	/**
	 * filter items by store
	 * @param $items
	 * @param null $limit
	 * @return array|string
	 */
    public function filterItems($items, $limit = null)
    {
        $storeId = $this->store->getStore()->getId();
        $count = 0;
        $results = array();
        foreach ($items as $item) {
        	$itemStoreIds = $item->getStoreIds();
			$itemStore = $itemStoreIds !== null ? explode(',', $itemStoreIds) : '';
			if (is_array($itemStore) && (in_array($storeId, $itemStore) || in_array('0', $itemStore))) {
				if ($limit && $count >= $limit) {
					break;
				}
				$count++;
				array_push($results, $item);
			}
        }

        if ($count == 0) {
            return '';
        }
		return $results;
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
	 * generate url_key for post, tag, topic, category, author
	 * @param $name
	 * @param $count
	 * @return string
	 */
	public function generateUrlKey($name, $count)
	{
		$name = $this->strReplace($name);
		$text = $this->translitUrl->filter($name);
		if ($count == 0) {
			$count = '';
		}
		if (empty($text)) {
			return 'n-a' . $count;
		}
		return $text . $count;
	}

	/**
	 * replace vietnamese characters to english characters
	 * @param $str
	 * @return mixed|string
	 */
	public function strReplace($str){

		$str = trim(mb_strtolower($str));
		$str = preg_replace('/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/', 'a', $str);
		$str = preg_replace('/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/', 'e', $str);
		$str = preg_replace('/(ì|í|ị|ỉ|ĩ)/', 'i', $str);
		$str = preg_replace('/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/', 'o', $str);
		$str = preg_replace('/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/', 'u', $str);
		$str = preg_replace('/(ỳ|ý|ỵ|ỷ|ỹ)/', 'y', $str);
		$str = preg_replace('/(đ)/', 'd', $str);
//			$str = preg_replace('/[^a-z0-9-\s]/', '', $str);
//			$str = preg_replace('/([\s]+)/', '-', $str);
		return $str;
	}

	/**
	 * get posts created_at
	 * @return array
	 */
	public function getPostDate()
	{
		$posts = $this->getPostList();
		$postDates = array();
		if($posts) {
			foreach ($posts as $post) {
				$postDates[] = $post->getCreatedAt();
			}
		}
		return $postDates;
	}

	/**
	 * get date label
	 * @return array
	 */
	public function getDateLabel(){
		$posts = $this->getPostList();
		$postDates = array();

		if($posts) {
			foreach ($posts as $post) {
				$postDates[] = $this->getDateFormat($post->getCreatedAt(), true);
			}
		}
		$result = array_values(array_unique($postDates));
		return $result;
	}

	/**
	 * get array of posts's date formated
	 * @return array
	 */
	public function getDateArray(){
		$dateArray = array();
		foreach ($this->getPostDate() as $postDate){
			$dateArray[] = date("F Y",$this->dateTime->timestamp($postDate));
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
		$limit = $this->getBlogConfig('monthly_archive/number_records') ?: 5;
		$dateArrayCount = $this->getDateArrayCount();
		$count = count($dateArrayCount);
		$result = ($count < $limit) ? $count : $limit ;
		return $result;
	}
	/**
	 * get author image link
	 * @return string
	 */
	public function getAuthorImageUrl($image)
	{
		return $this->getBaseMediaUrl(). self::AUTHOR_IMG . $image;
	}

	/**
	 * get date formatted
	 * @param $date
	 * @return false|string
	 */
	public function getDateFormat($date, $monthly = false)
	{
		if ($monthly) {
			$dateType = $this->getBlogConfig('monthly_archive/date_type_monthly');
			switch ($dateType) {
				case 1:
					$dateFormat = $this->dateTime->gmtDate("m - Y", $date);
					break;
				case 2:
					$dateFormat = $this->dateTime->gmtDate("Y - m", $date);
					break;
				case 3:
					$dateFormat = $this->dateTime->gmtDate("F Y", $date);
					break;
				default:
					$dateFormat = $this->dateTime->gmtDate('Y-m-d', $date);
					break;
			}
			return $dateFormat;
		}

		$dateType = $this->getBlogConfig('general/date_type');
		switch ($dateType) {
			case 2:
				$dateFormat = $this->dateTime->gmtDate("Y M d", $date);
				break;
			case 3:
				$dateFormat = $this->dateTime->gmtDate("d/m/Y", $date);
				break;
			case 4:
				$dateFormat = $this->dateTime->gmtDate("Y/m/d h:m:s", $date);
				break;
			default:
				$dateFormat = $this->dateTime->gmtDate('Y-m-d', $date);
				break;
		}

		return $dateFormat;
	}
}
