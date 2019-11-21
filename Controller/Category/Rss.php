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

namespace Mageplaza\Blog\Controller\Category;

use InvalidArgumentException;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Rss\Controller\Feed;

/**
 * Class Rss
 * @package Mageplaza\Blog\Controller\Category
 */
class Rss extends Feed
{
    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws NotFoundException
     * @throws InputException
     * @throws RuntimeException
     */
    public function execute()
    {
        $type = 'blog_categories';
        $categoryId = $this->getRequest()->getParam('category_id');
        if (!$categoryId) {
            return;
        }
        try {
            $provider = $this->rssManager->getProvider($type);
        } catch (InvalidArgumentException $e) {
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
