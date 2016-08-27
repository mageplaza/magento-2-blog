<?php
/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 * @category  Mageplaza
 * @package   Mageplaza_Blog
 * @copyright Copyright (c) 2016
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Block\Category;

use Mageplaza\Blog\Block\Frontend;

class Listpost extends Frontend
{
    protected function _prepareLayout()
    {
        $url          = $this->helperData->getCurrentUrl();
        $array        = explode('/', $url);
        $key          = array_search('category', $array) + 1;
        $categoryName = ($array[$key]);
        $category=$this->helperData->getCategoryByParam('url_key', $categoryName);
        $breadcrumbs  = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link'  => $this->_storeManager->getStore()->getBaseUrl()
                ]
            );
            $breadcrumbs->addCrumb(
                $this->helperData->getBlogConfig('general/url_prefix'),
                ['label' => ucfirst($this->helperData->getBlogConfig('general/url_prefix')),
                 'title' => $this->helperData->getBlogConfig('general/url_prefix'),
                 'link'  => $this->_storeManager->getStore()->getBaseUrl() . $this->helperData->getBlogConfig('general/url_prefix')]
            );
            $breadcrumbs->addCrumb(
                $categoryName,
                ['label' => ucfirst($category->getName()),
                 'title' => $category->getName()
                ]
            );
        }
        $this->applySeoCode($category);
    }

    public function getPostList()
    {
        return $this->helperData->getPostList('category', $this->getRequest()->getParam('id'));
    }

    public function checkRss()
    {
        $categoryId = $this->getRequest()->getParam('id');
        if (!$categoryId) {
            return false;
        }

        return $this->helperData->getBlogUrl('category/rss/category_id/' . $categoryId);
    }
}
