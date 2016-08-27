<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mageplaza\Blog\Block\Post\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;

/**
 * Class NewProducts
 * @package Magento\Catalog\Block\Rss\Product
 */
class Category extends \Magento\Framework\View\Element\AbstractBlock implements DataProviderInterface
{
    /**
     * @var \Mageplaza\Blog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Magento\Catalog\Model\Rss\Product\NewProducts
     */
    protected $rssModel;

    /**
     * @var \Magento\Framework\App\Rss\UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Mageplaza\Blog\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Catalog\Model\Rss\Product\NewProducts $rssModel
     * @param \Magento\Framework\App\Rss\UrlBuilderInterface $rssUrlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mageplaza\Blog\Model\CategoryFactory $rssModel,
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
        $this->setCacheKey('rss_blog_categories_store_' . $this->getStoreId());
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
        $categoryId=$this->getRequest()->getParam('category_id');
        $storeModel = $this->storeManager->getStore($this->getStoreId());
        $newUrl     = $this->rssUrlBuilder->getUrl(['store_id' => $this->getStoreId(), 'type' => 'blog_categories','category_id'=>$categoryId]);
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
        $category      = $this->rssModel->create()->load($categoryId);
        $posts=$category->getSelectedPostsCollection();
        $posts
            ->addFieldToFilter('enabled', 1)
            ->setOrder('post_id', 'DESC');
        foreach ($posts as $item) {
            $count++;
            if ($count > $limit) {
                break;
            }
            /** @var $item \Magento\Catalog\Model\Product */
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
    protected function getStoreId()
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
            $url  = $this->rssUrlBuilder->getUrl(['type' => 'blog_categories']);
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
