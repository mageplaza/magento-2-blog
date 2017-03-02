<?php

namespace Mageplaza\Blog\Helper;

use Mageplaza\Core\Helper\AbstractData as CoreHelper;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\Context;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\TagFactory;
use Mageplaza\Blog\Model\TopicFactory;
use Magento\Framework\View\Element\Template\Context as TemplateContext;

class Data extends CoreHelper
{
    const XML_PATH_BLOG = 'blog/';
    const POST_IMG = 'mageplaza/blog/post/image';

    protected $postfactory;
    protected $categoryfactory;
    protected $tagfactory;
    protected $topicfactory;
    protected $_store;

    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        PostFactory $postFactory,
        CategoryFactory $categoryFactory,
        TagFactory $tagFactory,
        TopicFactory $topicFactory,
        TemplateContext $templateContext
    ) {
    
        $this->postfactory     = $postFactory;
        $this->categoryfactory = $categoryFactory;
        $this->tagfactory      = $tagFactory;
        $this->topicfactory    = $topicFactory;
        $this->_store = $templateContext->getStoreManager();
        parent::__construct($context, $objectManager, $templateContext->getStoreManager());
    }

    public function getBlogConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_BLOG . $code, $storeId);
    }

    public function getPostList($type = null, $id = null)
    {
        $list          = '';
        $posts         = $this->postfactory->create();
        $categoryModel = $this->categoryfactory->create();
        $tagModel      = $this->tagfactory->create();
        $topicModel    = $this->topicfactory->create();
        if ($type == null) {
            $list = $posts->getCollection();
        } elseif ($type == 'category') {
            $category = $categoryModel->load($id);
            $list     = $category->getSelectedPostsCollection();
        } elseif ($type == 'tag') {
            $tag  = $tagModel->load($id);
            $list = $tag->getSelectedPostsCollection();
        } elseif ($type == 'topic') {
            $topic = $topicModel->load($id);
            $list  = $topic->getSelectedPostsCollection();
        }

        if ($list->getSize()) {
            $list->setOrder('created_at', 'desc')
                ->addFieldToFilter('enabled', 1)
                ->addFieldToFilter('store_ids', ['eq' => $this->_store->getStore()->getId()]);
            return $list;
        }

        return $posts;
    }

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

    public function getUrlByPost($post)
    {
        if ($post->getUrlKey()) {
            $url_prefix = $this->getBlogConfig('general/url_prefix');
            $url_suffix = $this->getBlogConfig('general/url_suffix');

            $urlKey = '';
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

    public function getBlogUrl($code)
    {
        return $this->_getUrl($this->getBlogConfig('general/url_prefix') . '/' . $code);
    }

    public function getPostByUrl($url)
    {
        $url   = $this->checkSuffix($url);
        $posts = $this->postfactory->create()->load($url, 'url_key');
        return $posts;
    }

    public function checkSuffix($url)
    {
        $url_suffix = $this->getBlogConfig('general/url_suffix');
        if (strpos($url, $url_suffix) !== false) {
            $url = str_replace($url_suffix, '', $url);
        }

        return $url;
    }

    public function getPostsByTag($tag)
    {
        $posts      = $this->postfactory->create();
        $collection = $posts->getCollection()->addFieldToFilter('enabled', 1);
        ;
        $result = $this->filterItems($collection);
        if ($result == '') {
            return '';
        }
        return $result;
    }

    public function getPostsByCategory($category)
    {
        $collection = true;

        return $collection;
    }

    public function getImageUrl($image)
    {
        return $this->getBaseMediaUrl(). self::POST_IMG . $image;
    }

    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getCategoryUrl($category)
    {
        return $this->_getUrl($this->getBlogConfig('general/url_prefix') . '/category/' . $category->getUrlKey());
    }

    public function getTagUrl($tag)
    {
        return $this->_getUrl($this->getBlogConfig('general/url_prefix') . '/tag/' . $tag->getUrlKey());
    }

    public function getTopicUrl($topic)
    {
        return $this->_getUrl($this->getBlogConfig('general/url_prefix') . '/topic/' . $topic->getUrlKey());
    }

    public function getPostCategoryHtml($post)
    {
        $categories = $this->getCategoryCollection($post->getCategoryIds());
        $categoryHtml = [];
        if (empty($categories)) {
            return null;
        } else {
            foreach ($categories as $_cat) {
                $categoryHtml[] = '<a class="mp-info" href="' . $this->getCategoryUrl($_cat) . '">' . $_cat->getName() . '</a>';
            }
        }
        $result = implode(', ', $categoryHtml);

        return $result;
    }

    public function getPost($id)
    {
        $post = $this->postfactory->create()->load($id);
        return $post;
    }
    public function getCategoryByParam($code, $param)
    {
        if ($code == 'id') {
            return $this->categoryfactory->create()->load($param);
        } else {
            return $this->categoryfactory->create()->load($param, $code);
        }
    }
    public function getTagByParam($code, $param)
    {
        if ($code == 'id') {
            return $this->tagfactory->create()->load($param);
        } else {
            return $this->tagfactory->create()->load($param, $code);
        }
    }
    public function getTopicByParam($code, $param)
    {
        if ($code == 'id') {
            return $this->topicfactory->create()->load($param);
        } else {
            return $this->topicfactory->create()->load($param, $code);
        }
    }
    public function getCategoryByPost($postId)
    {
        $post = $this->postfactory->create()->load($postId);
        return $post->getSelectedCategoriesCollection();
    }
    public function getTagsByPost($postId)
    {
        $post = $this->postfactory->create()->load($postId);
        return $post->getSelectedTagsCollection();
    }
    public function getTopicByPost($postId)
    {
        $post = $this->postfactory->create()->load($postId);
        return $post->getSelectedTopicsCollection();
    }

    public function getCurrentUrl()
    {
        $model=$this->objectManager->get('Magento\Framework\UrlInterface');
        return $model->getCurrentUrl();
    }

    /**
     * get most view post
     */
    public function getMosviewPosts()
    {
        $ob    = $this->objectManager->get('Mageplaza\Blog\Model\Traffic');
        $posts = $ob->getCollection()->addFieldToFilter('enabled', 1);
        ;
        $posts->join(
            'mageplaza_blog_post',
            'main_table.post_id=mageplaza_blog_post.post_id',
            '*'
        );
        $posts->setOrder('numbers_view', 'DESC');
        $postList = $this->filterItems($posts, $this->getBlogConfig('sidebar/number_mostview_posts'));
        if ($postList == '') {
            return '';
        }
        return $postList;
    }

    /**
     * get recent post
     */
    public function getRecentPost()
    {
        $posts = $this->postfactory->create()
            ->getCollection()
            ->addFieldToFilter('enabled', 1)
            ->setOrder('created_at', 'DESC');
        $postList = $this->filterItems($posts, $this->getBlogConfig('sidebar/number_recent_posts'));
        if ($postList == '') {
            return '';
        }
        return $postList;
    }

    /**
     * filter items by store
     */
    public function filterItems($items, $limit = null)
    {
        $storeId = $this->_store->getStore()->getId();
        $list = [];
        $count = 0;
        foreach ($items as $item) {
            $itemStore = $item->getStoreIds() ? explode(',', $item->getStoreIds()) : '-1';
            if (in_array($storeId, $itemStore)) {
                $count++;
                if ($limit && $count > $limit) {
                    break;
                }
                array_push($list, $item);
            }
        }
        
        if ($count == 0) {
            return '';
        }

        return $list;
    }
}
