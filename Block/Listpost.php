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
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;
use Magento\Theme\Block\Html\Pager;
use Mageplaza\Blog\Model\Config\Source\DisplayType;
use Mageplaza\Blog\Model\Post;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;

/**
 * Class Listpost
 * @package Mageplaza\Blog\Block\Post
 */
class Listpost extends Frontend implements IdentityInterface
{
    /**
     * @return Collection
     * @throws LocalizedException
     */
    public function getPostCollection()
    {
        $collection = $this->getCollection();

        if ($collection && $collection->getSize()) {
            $pager = $this->getLayout()->createBlock(Pager::class, 'mpblog.post.pager');

            $perPageValues = (string) $this->helperData
                ->getDisplayConfig('pagination', $this->store->getStore()->getId());

            $perPageValues = explode(',', $perPageValues ?? '');
            $perPageValues = array_combine($perPageValues, $perPageValues);
            $pager->setAvailableLimit($perPageValues)
                ->setCollection($collection);

            $this->setChild('pager', $pager);
        }

        return $collection;
    }

    /**
     * find /n in text
     *
     * @param $description
     *
     * @return string
     */
    public function maxShortDescription($description)
    {
        if (is_string($description)) {
            $html = '';
            foreach (explode("\n", trim($description)) as $value) {
                $html .= '<p>' . $value . '</p>';
            }

            return $html;
        }

        return $description;
    }

    /**
     * @return Collection
     */
    protected function getCollection()
    {
        try {
            return $this->helperData->getPostCollection(null, null, $this->store->getStore()->getId());
        } catch (Exception $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return null;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return bool
     */
    public function isGridView()
    {
        return $this->helperData->getPostViewPageConfig('display_style') == DisplayType::GRID;
    }

    /**
     * @return Listpost
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareLayout()
    {
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link'  => $this->_storeManager->getStore()->getBaseUrl()
            ])
                ->addCrumb($this->helperData->getRoute(), $this->getBreadcrumbsData());
        }

        $this->applySeoCode();

        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    protected function getBreadcrumbsData()
    {
        $label = $this->helperData->getBlogName();

        $data = [
            'label' => $label,
            'title' => $label
        ];

        if ($this->getRequest()->getFullActionName() !== 'mpblog_post_index') {
            $data['link'] = $this->helperData->getBlogUrl();
        }

        return $data;
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function applySeoCode()
    {
        $this->pageConfig->getTitle()->set(join($this->getTitleSeparator(), array_reverse($this->getBlogTitle(true))));

        $object      = $this->getBlogObject();
        $storeId     = $this->store->getStore()->getId();
        $description = $object ? $object->getMetaDescription() : $this->helperData->getBlogConfig('seo/meta_description', $storeId);
        $this->pageConfig->setDescription($description);

        $keywords = $object ? $object->getMetaKeywords() : $this->helperData->getBlogConfig('seo/meta_keywords', $storeId);
        $this->pageConfig->setKeywords($keywords);

        $robots = $object ? $object->getMetaRobots() : $this->helperData->getBlogConfig('seo/meta_robots', $storeId);
        $this->pageConfig->setRobots($robots);

        $url = $object ? $object->getUrl() : $this->helperData->getBlogConfig('seo/url_key', $storeId);

        if ($this->getRequest()->getFullActionName() === 'mpblog_post_view' && $url) {
            $this->pageConfig->addRemotePageAsset(
                $url,
                'canonical',
                ['attributes' => ['rel' => 'canonical']]
            );
        }
        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($this->getBlogTitle());
        }

        return $this;
    }

    /**
     * Retrieve HTML title value separator (with space)
     *
     * @return string
     */
    public function getTitleSeparator()
    {
        $separator = (string) $this->helperData->getConfigValue('catalog/seo/title_separator');

        return ' ' . $separator . ' ';
    }

    /**
     * @param bool $meta
     *
     * @return array|Phrase
     */
    public function getBlogTitle($meta = false)
    {
        $pageTitle = $this->helperData->getDisplayConfig('name') ?: __('Blog');
        if ($meta) {
            $title = $this->helperData->getBlogConfig('seo/meta_title') ?: $pageTitle;

            return [$title];
        }

        return $pageTitle;
    }

    public function getIdentities()
    {
        return [
            Post::CACHE_TAG
        ];
    }
}
