<?php
namespace Mageplaza\Blog\Block\Html;

use Mageplaza\Blog\Block\Frontend;

class Init extends Frontend
{
    protected function _construct()
    {
        if($this->checkConfig()) {
            $page = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\View\Page\Config');
            $page->addPageAsset('Mageplaza_Blog::css/index/mp.css');
        }
    }

    public function checkConfig(){
        return $this->getBlogConfig('general/enable_mpbootstrap');
    }
}
