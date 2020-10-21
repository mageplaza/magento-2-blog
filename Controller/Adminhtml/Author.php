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

namespace Mageplaza\Blog\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\AuthorFactory;

/**
 * Class Author
 * @package Mageplaza\Blog\Controller\Adminhtml
 */
abstract class Author extends Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_Blog::author';

    /**
     * @var Registry
     */
    public $coreRegistry;

    /**
     * @var AuthorFactory
     */
    public $authorFactory;

    /**
     * Author constructor.
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param AuthorFactory $authorFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        AuthorFactory $authorFactory
    ) {
        $this->authorFactory = $authorFactory;
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @param bool $register
     *
     * @return bool|\Mageplaza\Blog\Model\Author
     */
    public function initAuthor($register = false)
    {
        $authorId = (int)$this->getRequest()->getParam('id');

        /** @var \Mageplaza\Blog\Model\Author $author */
        $author = $this->authorFactory->create();
        if ($authorId) {
            $author->load($authorId);
            if (!$author->getId()) {
                $this->messageManager->addErrorMessage(__('This author no longer exists.'));

                return false;
            }
        }

        if ($register) {
            $this->coreRegistry->register('mageplaza_blog_author', $author);
        }

        return $author;
    }
}
