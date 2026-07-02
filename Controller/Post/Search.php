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

namespace Mageplaza\Blog\Controller\Post;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\LayoutInterface;
use Mageplaza\Blog\Block\Sidebar\Search as SearchBlock;

/**
 * AJAX blog search: returns a small, query-filtered list of posts instead of
 * dumping the whole post table into every page (which OOMs at scale).
 *
 * @package Mageplaza\Blog\Controller\Post
 */
class Search extends Action implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param LayoutInterface $layout
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        LayoutInterface $layout
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layout            = $layout;

        parent::__construct($context);
    }

    /**
     * @return Json
     */
    public function execute()
    {
        $result      = $this->resultJsonFactory->create();
        $query       = (string) $this->getRequest()->getParam('query', '');
        $suggestions = [];

        try {
            /** @var SearchBlock $block */
            $block       = $this->layout->createBlock(SearchBlock::class);
            $suggestions = $block->getSearchSuggestions($query);
        } catch (Exception $e) {
            $suggestions = [];
        }

        return $result->setData(['query' => $query, 'suggestions' => $suggestions]);
    }
}
