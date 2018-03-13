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
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Sitemap\Helper\Data;
use Mageplaza\Blog\Helper\Image;

/**
 * Class Sitemap
 * @package Mageplaza\Blog\Model
 */
class Sitemap extends \Magento\Sitemap\Model\Sitemap
{
    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    protected $blogDataHelper;

    /**
     * @var \Mageplaza\Blog\Helper\Image
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
        $this->imageHelper = ObjectManager::getInstance()->get(Image::class);;
        $this->router = $this->blogDataHelper->getBlogConfig('general/url_prefix');
    }

    /**
     * @return array
     */
    public function getBlogPostsSiteMapCollection()
    {
        $postCollection = $this->blogDataHelper->postFactory->create()->getCollection();
        $postSiteMapCollection = [];
        if (!$this->router) {
            $this->router = 'blog';
        }
        foreach ($postCollection as $item) {
            if (!is_null($item->getEnabled())) {
                $images = null;
                if ($item->getImage()) :
                    $imageFile = $this->imageHelper->getMediaPath($item->getImage(), Image::TEMPLATE_MEDIA_TYPE_POST);

                    $imagesCollection[] = new DataObject([
                            'url' => $this->imageHelper->getMediaUrl($imageFile),
                            'caption' => null,
                        ]
                    );
                    $images = new DataObject(['collection' => $imagesCollection]);
                endif;
                $postSiteMapCollection[$item->getId()] = new DataObject([
                    'id' => $item->getId(),
                    'url' => $this->router . '/post/' . $item->getUrlKey(),
                    'images' => $images,
                    'updated_at' => $item->getUpdatedAt(),
                ]);
            }
        }

        return $postSiteMapCollection;
    }

    /**
     * @return array
     */
    public function getBlogCategoriesSiteMapCollection()
    {
        $categoryCollection = $this->blogDataHelper->categoryFactory->create()->getCollection();
        $categorySiteMapCollection = [];
        foreach ($categoryCollection as $item) {
            if (!is_null($item->getEnabled())) {
                $categorySiteMapCollection[$item->getId()] = new DataObject([
                    'id' => $item->getId(),
                    'url' => $this->router . '/category/' . $item->getUrlKey(),
                    'updated_at' => $item->getUpdatedAt(),
                ]);
            }
        }

        return $categorySiteMapCollection;
    }

    /**
     * @return array
     */
    public function getBlogTagsSiteMapCollection()
    {
        $tagCollection = $this->blogDataHelper->tagFactory->create()->getCollection();
        $tagSiteMapCollection = [];
        foreach ($tagCollection as $item) {
            if (!is_null($item->getEnabled())) {
                $tagSiteMapCollection[$item->getId()] = new DataObject([
                    'id' => $item->getId(),
                    'url' => $this->router . '/tag/' . $item->getUrlKey(),
                    'updated_at' => $item->getUpdatedAt(),
                ]);
            }
        }

        return $tagSiteMapCollection;
    }

    /**
     * @return array
     */
    public function getBlogTopicsSiteMapCollection()
    {
        $topicCollection = $this->blogDataHelper->topicFactory->create()->getCollection();
        $topicSiteMapCollection = [];
        foreach ($topicCollection as $item) {
            if (!is_null($item->getEnabled())) {
                $topicSiteMapCollection[$item->getId()] = new DataObject([
                    'id' => $item->getId(),
                    'url' => $this->router . '/topic/' . $item->getUrlKey(),
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
            ]
        );
        $this->_sitemapItems[] = new DataObject([
                'collection' => $this->getBlogCategoriesSiteMapCollection(),
            ]
        );
        $this->_sitemapItems[] = new DataObject([
                'collection' => $this->getBlogTagsSiteMapCollection(),
            ]
        );
        $this->_sitemapItems[] = new DataObject([
                'collection' => $this->getBlogTopicsSiteMapCollection(),
            ]
        );

        parent::_initSitemapItems(); // TODO: Change the autogenerated stub
    }
}
