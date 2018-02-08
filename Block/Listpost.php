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

namespace Mageplaza\Blog\Block;

use Mageplaza\Blog\Model\Config\Source\DisplayType;

/**
 * Class Listpost
 * @package Mageplaza\Blog\Block\Post
 */
class Listpost extends Frontend
{
    /**
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPostCollection()
    {
        $collection = $this->getCollection();

        if ($collection && $collection->getSize()) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager', 'mpblog.post.pager');

            $perPageValues = (string)$this->helperData->getConfigGeneral('pagination');;
            $perPageValues = explode(',', $perPageValues);
            $perPageValues = array_combine($perPageValues, $perPageValues);

            $pager->setAvailableLimit($perPageValues)
                ->setCollection($collection);

            $this->setChild('pager', $pager);
        }

        return $collection;
    }

    /**
     * Override this function to apply collection for each type
     *
     * @return \Mageplaza\Blog\Model\ResourceModel\Post\Collection
     */
    protected function getCollection()
    {
        return $this->helperData->getPostCollection();
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
        return $this->helperData->getBlogConfig('general/display_style') == DisplayType::GRID;
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('home', [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->_storeManager->getStore()->getBaseUrl()
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

        if ($this->getRequest()->getFullActionName() != 'mpblog_post_index') {
            $data['link'] = $this->helperData->getBlogUrl();
        }

        return $data;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function applySeoCode()
    {
        $this->pageConfig->getTitle()->set(join($this->getTitleSeparator(), array_reverse($this->getBlogTitle(true))));

        $object = $this->getBlogObject();

        $description = $object ? $object->getMetaDescription() : $this->helperData->getSeoConfig('meta_description');
        $this->pageConfig->setDescription($description);

        $keywords = $object ? $object->getMetaKeywords() : $this->helperData->getSeoConfig('meta_keywords');
        $this->pageConfig->setKeywords($keywords);

        $robots = $object ? $object->getMetaRobots() : $this->helperData->getSeoConfig('meta_robots');
        $this->pageConfig->setRobots($robots);

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
        $separator = (string)$this->helperData->getConfigValue('catalog/seo/title_separator');

        return ' ' . $separator . ' ';
    }

    /**
     * @param bool $meta
     * @return array
     */
    public function getBlogTitle($meta = false)
    {
        $pageTitle = $this->helperData->getConfigGeneral('name') ?: __('Blog');
        if ($meta) {
            $title = $this->helperData->getSeoConfig('meta_title') ?: $pageTitle;

            return [$title];
        }

        return $pageTitle;
    }
}
