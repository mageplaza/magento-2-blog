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

use \Magento\Framework\Model\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\Escaper;
use \Magento\Sitemap\Helper\Data;
use \Magento\Framework\Filesystem;
use \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory;
use \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory;
use \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Model\ResourceModel\AbstractResource;
use \Magento\Framework\Data\Collection\AbstractDb;
use \Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\DataObject;
use Mageplaza\Blog\Helper\Data as BlogDataHelper;
use Mageplaza\Blog\Helper\Image as BlogImageHelper;
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
     * Sitemap constructor.
     * @param Context $context
     * @param Registry $registry
     * @param Escaper $escaper
     * @param Data $sitemapData
     * @param Filesystem $filesystem
     * @param CategoryFactory $categoryFactory
     * @param ProductFactory $productFactory
     * @param PageFactory $cmsFactory
     * @param DateTime $modelDate
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param BlogDataHelper $blogDataHelper
     * @param BlogImageHelper $blogImageHelper
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param DocumentRoot|null $documentRoot
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Escaper $escaper,
        \Magento\Sitemap\Helper\Data $sitemapData,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sitemap\Model\ResourceModel\Catalog\CategoryFactory $categoryFactory,
        \Magento\Sitemap\Model\ResourceModel\Catalog\ProductFactory $productFactory,
        \Magento\Sitemap\Model\ResourceModel\Cms\PageFactory $cmsFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $modelDate,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        BlogDataHelper  $blogDataHelper,
        BlogImageHelper $blogImageHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        DocumentRoot $documentRoot = null
    ) {
        $this->blogDataHelper   =   $blogDataHelper;
        $this->imageHelper      =   $blogImageHelper;
        $this->router           =   $this->blogDataHelper->getBlogConfig('general/url_prefix');

        parent::__construct(
            $context,
            $registry,
            $escaper,
            $sitemapData,
            $filesystem,
            $categoryFactory,
            $productFactory,
            $cmsFactory,
            $modelDate,
            $storeManager,
            $request,
            $dateTime,
            $resource,
            $resourceCollection,
            $data,
            $documentRoot
        );
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
                    $imageFile = $this->imageHelper->getMediaPath(
                        $item->getImage(),
                        \Mageplaza\Blog\Helper\Image::TEMPLATE_MEDIA_TYPE_POST
                    );

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
