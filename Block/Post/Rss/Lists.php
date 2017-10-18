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

namespace Mageplaza\Blog\Block\Post\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Helper\Data;

/**
 * Class NewProducts
 * @package Magento\Catalog\Block\Rss\Product
 */
class Lists extends AbstractBlock implements DataProviderInterface
{
    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     */
    public $rssUrlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    public $helper;

    /**
     * Lists constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageplaza\Blog\Helper\Data $helper
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     */
    public function __construct(
        Context $context,
        UrlBuilderInterface $rssUrlBuilder,
        Data $helper,
        array $data = []
    )
    {
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->helper        = $helper;
        $this->storeManager  = $context->getStoreManager();

        parent::__construct($context, $data);
    }

    /**
     * @return void
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
        $title      = __('List Posts from %1', $storeModel->getFrontendName());
        $data       = [
            'title'       => $title,
            'description' => $title,
            'link'        => $this->rssUrlBuilder->getUrl(['store_id' => $this->getStoreId(), 'type' => 'blog_posts']),
            'charset'     => 'UTF-8',
            'language'    => $this->helper->getConfigValue('general/locale/code', $storeModel),
        ];

        $posts = $this->helper->getPostList($this->getStoreId())
            ->addFieldToFilter('in_rss', 1)
            ->setOrder('post_id', 'DESC');
        $posts->getSelect()
            ->limit(10);
        foreach ($posts as $item) {
            $item->setAllowedInRss(true);
            $item->setAllowedPriceInRss(true);

            $description       = '<table><tr><td style="text-decoration:none;"> ' . $item->getShortDescription() . '</td></tr></table>';
            $data['entries'][] = [
                'title'       => $item->getName(),
                'link'        => $item->getUrl(),
                'description' => $description,
                'lastUpdate'  => strtotime($item->getPublishDate())
            ];
        }

        return $data;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        if ($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        return $storeId;
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
            $url  = $this->rssUrlBuilder->getUrl(['type' => 'blog_posts']);
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
