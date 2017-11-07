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

namespace Mageplaza\Blog\Controller\Adminhtml;

/**
 * Class Author
 * @package Mageplaza\Blog\Controller\Adminhtml
 */
abstract class Author extends \Magento\Backend\App\Action
{
    /** Authorization level of a basic admin session */
    const ADMIN_RESOURCE = 'Mageplaza_Blog::author';

    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * @var \Mageplaza\Blog\Model\AuthorFactory
     */
    public $authorFactory;

    /**
     * Author constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Mageplaza\Blog\Model\AuthorFactory $authorFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Mageplaza\Blog\Model\AuthorFactory $authorFactory
    )
    {
        $this->authorFactory = $authorFactory;
        $this->coreRegistry  = $coreRegistry;

        parent::__construct($context);
    }

    /**
     * @return \Mageplaza\Blog\Model\Author
     */
    public function initAuthor()
    {
        $user   = $this->_auth->getUser();
        $userId = $user->getId();

        /** @var \Mageplaza\Blog\Model\Author $author */
        $author = $this->authorFactory->create()
            ->load($userId);

        if (!$author->getId()) {
            $author->setId($userId)
                ->setName($user->getName());
        }

        return $author;
    }
}
