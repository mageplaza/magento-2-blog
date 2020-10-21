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

namespace Mageplaza\Blog\Block\Post\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\Post;

/**
 * Class NewProducts
 * @package Magento\Catalog\Block\Rss\Product
 */
class Lists extends AbstractBlock implements DataProviderInterface
{
    /**
     * @var UrlBuilderInterface
     */
    public $rssUrlBuilder;

    /**
     * @var StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var Data
     */
    public $helper;

    /**
     * Lists constructor.
     *
     * @param Context $context
     * @param Data $helper
     * @param UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlBuilderInterface $rssUrlBuilder,
        Data $helper,
        array $data = []
    ) {
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->helper = $helper;
        $this->storeManager = $context->getStoreManager();

        parent::__construct($context, $data);
    }

    /**
     * @throws NoSuchEntityException
     */
    protected function _construct()
    {
        $this->setCacheKey('rss_blog_posts_store_' . $this->getStoreId());

        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed()
    {
        return $this->helper->isEnabled();
    }

    /**
     * {@inheritdoc}
     */
    public function getRssData()
    {
        $storeModel = $this->storeManager->getStore($this->getStoreId());
        $title = __('List Posts from %1', $storeModel->getFrontendName())->render();
        $storeUrl = $this->storeManager->getStore($this->getStoreId())->getBaseUrl(UrlInterface::URL_TYPE_WEB);
        $data = [
            'title' => $title,
            'description' => $title,
            'link' => $storeUrl . 'blog/post/rss.xml',
            'charset' => 'UTF-8',
            'language' => $this->helper->getConfigValue('general/locale/code', $storeModel),
        ];

        $posts = $this->helper->getPostList($this->getStoreId())
            ->addFieldToFilter('in_rss', 1)
            ->setOrder('post_id', 'DESC');
        $posts->getSelect()->limit(10);
        /** @var Post $item */
        foreach ($posts->getItems() as $item) {
            $item->setAllowedInRss(true);
            $item->setAllowedPriceInRss(true);

            $description = $item->getShortDescription();
            $data['entries'][] = [
                'title' => $item->getName(),
                'link' => $item->getUrl(),
                'description' => $description ?: 'no content',
                'lastUpdate' => strtotime($item->getPublishDate())
            ];
        }

        return $data;
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getStoreId()
    {
        $storeId = $this->getRequest()->getParam('store_id');
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return (int)$storeId;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheLifetime()
    {
        return 1;
    }

    /**
     * @return array
     */
    public function getFeeds()
    {
        $data = [];
        if ($this->isAllowed()) {
            $url = $this->rssUrlBuilder->getUrl(['type' => 'blog_posts']);
            $data = ['label' => __('Posts'), 'link' => $url];
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthRequired()
    {
        return false;
    }
}
