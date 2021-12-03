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

namespace Mageplaza\Blog\Block;

use Exception;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Url;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Category as CategoryOptions;
use Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Tag as TagOptions;
use Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Topic as TopicOptions;
use Mageplaza\Blog\Helper\Data as HelperData;
use Mageplaza\Blog\Helper\Image;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\CommentFactory;
use Mageplaza\Blog\Model\Config\Source\AuthorStatus;
use Mageplaza\Blog\Model\LikeFactory;
use Mageplaza\Blog\Model\Post;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\PostLikeFactory;

/**
 * Class Frontend
 * @package Mageplaza\Blog\Block
 */
class Frontend extends Template
{
    /**
     * @var FilterProvider
     */
    public $filterProvider;

    /**
     * @var HelperData
     */
    public $helperData;

    /**
     * @var StoreManagerInterface
     */
    public $store;

    /**
     * @var CommentFactory
     */
    public $cmtFactory;

    /**
     * @var LikeFactory
     */
    public $likeFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    public $customerRepository;

    /**
     * @var
     */
    public $commentTree;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var DateTime
     */
    public $dateTime;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var CategoryOptions
     */
    protected $categoryOptions;

    /**
     * @var TopicOptions
     */
    protected $topicOptions;

    /**
     * @var TagOptions
     */
    protected $tagOptions;

    /**
     * @var PostLikeFactory
     */
    protected $postLikeFactory;

    /**
     * @var AuthorStatus
     */
    protected $authorStatusType;

    /**
     * @var ThemeProviderInterface
     */
    protected $themeProvider;

    /**
     * @var EncryptorInterface
     */
    public $enc;

    /**
     * Frontend constructor.
     *
     * @param Context $context
     * @param FilterProvider $filterProvider
     * @param CommentFactory $commentFactory
     * @param LikeFactory $likeFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param Registry $coreRegistry
     * @param HelperData $helperData
     * @param Url $customerUrl
     * @param CategoryFactory $categoryFactory
     * @param PostFactory $postFactory
     * @param DateTime $dateTime
     * @param PostLikeFactory $postLikeFactory
     * @param CategoryOptions $category
     * @param TopicOptions $topic
     * @param TagOptions $tag
     * @param ThemeProviderInterface $themeProvider
     * @param EncryptorInterface $enc
     * @param AuthorStatus $authorStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        FilterProvider $filterProvider,
        CommentFactory $commentFactory,
        LikeFactory $likeFactory,
        CustomerRepositoryInterface $customerRepository,
        Registry $coreRegistry,
        HelperData $helperData,
        Url $customerUrl,
        CategoryFactory $categoryFactory,
        PostFactory $postFactory,
        DateTime $dateTime,
        PostLikeFactory $postLikeFactory,
        CategoryOptions $category,
        TopicOptions $topic,
        TagOptions $tag,
        ThemeProviderInterface $themeProvider,
        EncryptorInterface $enc,
        AuthorStatus $authorStatus,
        array $data = []
    ) {
        $this->filterProvider     = $filterProvider;
        $this->cmtFactory         = $commentFactory;
        $this->likeFactory        = $likeFactory;
        $this->customerRepository = $customerRepository;
        $this->helperData         = $helperData;
        $this->coreRegistry       = $coreRegistry;
        $this->dateTime           = $dateTime;
        $this->categoryFactory    = $categoryFactory;
        $this->postFactory        = $postFactory;
        $this->customerUrl        = $customerUrl;
        $this->postLikeFactory    = $postLikeFactory;
        $this->categoryOptions    = $category;
        $this->topicOptions       = $topic;
        $this->tagOptions         = $tag;
        $this->authorStatusType   = $authorStatus;
        $this->themeProvider      = $themeProvider;
        $this->store              = $context->getStoreManager();
        $this->enc                = $enc;

        parent::__construct($context, $data);
    }

    /**
     * @return HelperData
     */
    public function getBlogHelper()
    {
        return $this->helperData;
    }

    /**
     * @return bool
     */
    public function isBlogEnabled()
    {
        return $this->helperData->isEnabled();
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public function getPageFilter($content)
    {
        try {
            return $this->filterProvider->getPageFilter()->filter((string) $content);
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @param string $image
     * @param string $type
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getImageUrl($image, $type = Image::TEMPLATE_MEDIA_TYPE_POST)
    {
        $imageHelper = $this->helperData->getImageHelper();
        $imageFile   = $imageHelper->getMediaPath($image, $type);

        return $this->helperData->getImageHelper()->getMediaUrl($imageFile);
    }

    /**
     * @param string|Object $urlKey
     * @param null $type
     *
     * @return string
     */
    public function getRssUrl($urlKey, $type = null)
    {
        if (is_object($urlKey)) {
            $urlKey = $urlKey->getUrlKey();
        }

        $urlKey = ($type ? $type . '/' : '') . $urlKey;
        $url    = $this->helperData->getUrl($this->helperData->getRoute() . '/' . $urlKey);

        return rtrim($url, '/') . '.xml';
    }

    /**
     * @param Post $post
     *
     * @return Phrase|string
     */
    public function getPostInfo($post)
    {
        try {
            $likeCollection = $this->postLikeFactory->create()->getCollection();
            $couldLike      = $likeCollection->addFieldToFilter('post_id', $post->getId())
                ->addFieldToFilter('action', '1')->count();
            $html           = __(
                '<i class="mp-blog-icon mp-blog-calendar-times"></i> %1',
                $this->getDateFormat($post->getPublishDate())
            );

            if ($categoryPost = $this->getPostCategoryHtml($post)) {
                $html .= __('| Posted in %1', $categoryPost);
            }

            $author = $this->helperData->getAuthorByPost($post);
            if ($author && $author->getName() && $this->helperData->showAuthorInfo()) {
                $aTag = '<a class="mp-info" href="' . $author->getUrl() . '">'
                    . $this->escapeHtml($author->getName()) . '</a>';
                $html .= __('| <i class="mp-blog-icon mp-blog-user"></i> %1', $aTag);
            }

            if ($this->getCommentinPost($post)) {
                $html .= __(
                    '| <i class="mp-blog-icon mp-blog-comments" aria-hidden="true"></i> %1',
                    $this->getCommentinPost($post)
                );
            }

            if ($post->getViewTraffic()) {
                $html .= __(
                    '| <i class="mp-blog-icon mp-blog-traffic" aria-hidden="true"></i> %1',
                    $post->getViewTraffic()
                );
            }

            if ($couldLike > 0) {
                $html .= __('| <i class="mp-blog-icon mp-blog-thumbs-up" aria-hidden="true"></i> %1', $couldLike);
            }
        } catch (Exception $e) {
            $html = '';
        }

        return $html;
    }

    /**
     * @param Post $post
     *
     * @return int
     */
    public function getCommentinPost($post)
    {
        $cmt = $this->cmtFactory->create()->getCollection()->addFieldToFilter('post_id', $post->getId());

        return $cmt->count();
    }

    /**
     * Get list category html of post
     *
     * @param Post $post
     *
     * @return string|null
     */
    public function getPostCategoryHtml($post)
    {
        $categoryHtml = [];

        try {
            if (!$post->getCategoryIds()) {
                return null;
            }

            $categories = $this->helperData->getCategoryCollection($post->getCategoryIds());
            foreach ($categories as $_cat) {
                $categoryHtml[] = '<a class="mp-info" href="'
                    . $this->helperData->getBlogUrl(
                        $_cat,
                        HelperData::TYPE_CATEGORY
                    )
                    . '">' . $_cat->getName() . '</a>';
            }
        } catch (Exception $e) {
            return null;
        }

        return implode(', ', $categoryHtml);
    }

    /**
     * @param string $date
     * @param bool $monthly
     *
     * @return false|string|null
     */
    public function getDateFormat($date, $monthly = false)
    {
        try {
            $date = $this->helperData->getDateFormat($date, $monthly);
        } catch (Exception $e) {
            $date = null;
        }

        return $date;
    }

    /**
     * @param string $image
     * @param null $size
     * @param string $type
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function resizeImage($image, $size = null, $type = Image::TEMPLATE_MEDIA_TYPE_POST)
    {
        if (!$image) {
            return $this->getDefaultImageUrl();
        }

        return $this->helperData->getImageHelper()->resizeImage($image, $size, $type);
    }

    /**
     * get default image url
     */
    public function getDefaultImageUrl()
    {
        return $this->getViewFileUrl('Mageplaza_Blog::media/images/mageplaza-logo-default.png');
    }

    /**
     * @return string
     */
    public function getDefaultAuthorImage()
    {
        return $this->getViewFileUrl('Mageplaza_Blog::media/images/no-artist-image.jpg');
    }
}
