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

namespace Mageplaza\Blog\Controller\Adminhtml\Topic;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\LayoutFactory;
use Mageplaza\Blog\Model\TopicFactory;

/**
 * Class Posts
 * @package Mageplaza\Blog\Controller\Adminhtml\Topic
 */
class Posts extends \Mageplaza\Blog\Controller\Adminhtml\Topic
{
    /**
     * Result layout factory
     *
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    public $resultLayoutFactory;

    /**
     * Posts constructor.
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     * @param \Mageplaza\Blog\Model\TopicFactory $postFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        Context $context,
        Registry $registry,
        LayoutFactory $resultLayoutFactory,
        TopicFactory $postFactory
    )
    {
        $this->resultLayoutFactory = $resultLayoutFactory;

        parent::__construct($context, $registry, $postFactory);
    }

    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initTopic(true);

        return $this->resultLayoutFactory->create();
    }
}
