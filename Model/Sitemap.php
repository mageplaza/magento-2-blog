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

namespace Mageplaza\Blog\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Image;

/**
 * Class Sitemap
 * @package Mageplaza\Blog\Model
 */
class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * @var Data
     */
    protected $blogDataHelper;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var mixed
     */
    protected $router;

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->blogDataHelper = ObjectManager::getInstance()->get(Data::class);
        $this->imageHelper = ObjectManager::getInstance()->get(Image::class);
        $this->router = $this->blogDataHelper->getBlogConfig('general/url_prefix');
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getBlogPostsSiteMapCollection()
    {
        $urlSuffix = $this->blogDataHelper->getUrlSuffix();
        $postCollection = $this->blogDataHelper->postFactory->create()->getCollection();
        $currentStoreId = $this->getStoreId();
        $postCollection = $this->blogDataHelper->addStoreFilter($postCollection, $currentStoreId);
        $postSiteMapCollection = [];
        if (!$this->router) {
            $this->router = 'blog';
        }
        foreach ($postCollection as $item) {
            if ($item->getEnabled() !== null) {
                $images = null;
                if ($item->getImage()) {
                    $imageFile = $this->imageHelper->getMediaPath($item->getImage(), Image::TEMPLATE_MEDIA_TYPE_POST);
                    $imagesCollection = [];
                    $imagesCollection[] = new DataObject([
                        'url' => $this->imageHelper->getMediaUrl($imageFile),
                        'caption' => null,
                    ]);
                    $images = new DataObject(['collection' => $imagesCollection, 'title' => $item->getName()]);
                }
                $postSiteMapCollection[$item->getId()] = new DataObject([
                    'id' => $item->getId(),
                    'url' => $this->router . '/post/' . $item->getUrlKey() . $urlSuffix,
                    'images' => $images,
                    'updated_at' => $item->getUpdatedAt(),
                ]);
            }
        }

        return $postSiteMapCollection;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getBlogCategoriesSiteMapCollection()
    {
        $urlSuffix = $this->blogDataHelper->getUrlSuffix();
        $categoryCollection = $this->blogDataHelper->categoryFactory->create()->getCollection();
        $categorySiteMapCollection = [];
        $currentStoreId = $this->getStoreId();
        $categoryCollection = $this->blogDataHelper->addStoreFilter($categoryCollection, $currentStoreId);
        foreach ($categoryCollection as $item) {
            if ($item->getEnabled() !== null) {
                $categorySiteMapCollection[$item->getId()] = new DataObject([
                    'id' => $item->getId(),
                    'url' => $this->router . '/category/' . $item->getUrlKey() . $urlSuffix,
                    'updated_at' => $item->getUpdatedAt(),
                ]);
            }
        }

        return $categorySiteMapCollection;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getBlogTagsSiteMapCollection()
    {
        $urlSuffix = $this->blogDataHelper->getUrlSuffix();
        $tagCollection = $this->blogDataHelper->tagFactory->create()->getCollection();
        $tagSiteMapCollection = [];
        $currentStoreId = $this->getStoreId();
        $tagCollection = $this->blogDataHelper->addStoreFilter($tagCollection, $currentStoreId);
        foreach ($tagCollection as $item) {
            if ($item->getEnabled() !== null) {
                $tagSiteMapCollection[$item->getId()] = new DataObject([
                    'id' => $item->getId(),
                    'url' => $this->router . '/tag/' . $item->getUrlKey() . $urlSuffix,
                    'updated_at' => $item->getUpdatedAt(),
                ]);
            }
        }

        return $tagSiteMapCollection;
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getBlogTopicsSiteMapCollection()
    {
        $urlSuffix = $this->blogDataHelper->getUrlSuffix();
        $topicCollection = $this->blogDataHelper->topicFactory->create()->getCollection();
        $topicSiteMapCollection = [];
        $currentStoreId = $this->getStoreId();
        $topicCollection = $this->blogDataHelper->addStoreFilter($topicCollection, $currentStoreId);
        foreach ($topicCollection as $item) {
            if ($item->getEnabled() !== null) {
                $topicSiteMapCollection[$item->getId()] = new DataObject([
                    'id' => $item->getId(),
                    'url' => $this->router . '/topic/' . $item->getUrlKey() . $urlSuffix,
                    'updated_at' => $item->getUpdatedAt(),
                ]);
            }
        }

        return $topicSiteMapCollection;
    }

    /**
     * @inheritdoc
     */
    public function _initSitemapItems()
    {
        $this->_sitemapItems[] = new DataObject([
            'collection' => $this->getBlogPostsSiteMapCollection(),
        ]);
        $this->_sitemapItems[] = new DataObject([
            'collection' => $this->getBlogCategoriesSiteMapCollection(),
        ]);
        $this->_sitemapItems[] = new DataObject([
            'collection' => $this->getBlogTagsSiteMapCollection(),
        ]);
        $this->_sitemapItems[] = new DataObject([
            'collection' => $this->getBlogTopicsSiteMapCollection(),
        ]);

        parent::_initSitemapItems(); // TODO: Change the autogenerated stub
    }
}
