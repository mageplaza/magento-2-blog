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
namespace Mageplaza\Blog\Block\Post\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;

/**
 * Class NewProducts
 * @package Magento\Catalog\Block\Rss\Product
 */
class Lists extends \Magento\Framework\View\Element\AbstractBlock implements DataProviderInterface
{
    /**
     * @var \Mageplaza\Blog\Helper\Image
     */
    public $imageHelper;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\NewProducts
     */
	public $rssModel;

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
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\Rss\Product\NewProducts $rssModel
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mageplaza\Blog\Model\PostFactory $rssModel,
        \Mageplaza\Blog\Helper\Data $helper,
        \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder,
        array $data = []
    ) {
    
        $this->helper        = $helper;
        $this->rssModel      = $rssModel;
        $this->rssUrlBuilder = $rssUrlBuilder;
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
        return $this->_scopeConfig->isSetFlag('blog/general/enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * {@inheritdoc}
     */
    public function getRssData()
    {
        $storeModel = $this->storeManager->getStore($this->getStoreId());
        $newUrl     = $this->rssUrlBuilder->getUrl(['store_id' => $this->getStoreId(), 'type' => 'blog_posts']);
        $title      = __('List Posts from %1', $storeModel->getFrontendName());
        $lang       = $this->_scopeConfig->getValue(
            'general/locale/code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeModel
        );
        $data       = [
            'title'       => $title,
            'description' => $title,
            'link'        => $newUrl,
            'charset'     => 'UTF-8',
            'language'    => $lang,
        ];
        $limit      = 10;
        $count      = 0;
        $posts      = $this->rssModel->create()->getCollection();
        $posts
            ->addFieldToFilter('enabled', 1)
            ->addFieldToFilter('in_rss', 1)
            ->setOrder('post_id', 'DESC');
        foreach ($posts as $item) {
            $count++;
            if ($count > $limit) {
                break;
            }
            $item->setAllowedInRss(true);
            $item->setAllowedPriceInRss(true);

            if (!$item->getAllowedInRss()) {
                continue;
            }

            $description = '
                <table><tr>
                <td style="text-decoration:none;"> %s</td>
                </tr></table>
            ';
            $description = sprintf(
                $description,
                $item->getShortDescription()
            );

            $data['entries'][] = [
                'title'       => $item->getName(),
                'link'        => $this->helper->getUrlByPost($item),
                'description' => $description,
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
