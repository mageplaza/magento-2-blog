<?php

namespace Mageplaza\Blog\Controller\Category;

use Magento\Framework\Exception\NotFoundException;

class Rss extends \Magento\Rss\Controller\Feed
{
    public function execute()
    {

        $type = 'blog_categories';
        $categoryId=$this->getRequest()->getParam('category_id');
        if (!$categoryId) {
            return;
        }
        try {
            $provider = $this->rssManager->getProvider($type);
        } catch (\InvalidArgumentException $e) {
            throw new NotFoundException(__($e->getMessage()));
        }

        if ($provider->isAuthRequired() && !$this->auth()) {
            return;
        }


        /** @var $rss \Magento\Rss\Model\Rss */
        $rss = $this->rssFactory->create();
        $rss->setDataProvider($provider);

        $this->getResponse()->setHeader('Content-type', 'text/xml; charset=UTF-8');
        $this->getResponse()->setBody($rss->createRssXml());
    }
}
