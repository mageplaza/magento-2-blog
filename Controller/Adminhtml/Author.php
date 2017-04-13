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
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Blog\Controller\Adminhtml;

abstract class Author extends \Magento\Backend\App\Action
{

	public $authorFactory;


	public $coreRegistry;


	public $resultRedirectFactory;

	public function __construct(
		\Mageplaza\Blog\Model\AuthorFactory $authorFactory,
		\Magento\Framework\Registry $coreRegistry,
		\Magento\Backend\App\Action\Context $context
	) {

		$this->authorFactory         = $authorFactory;
		$this->coreRegistry          = $coreRegistry;
		$this->resultRedirectFactory = $context->getRedirect();
		parent::__construct($context);
	}


	public function initAuthor()
	{
		$author    = $this->authorFactory->create();
		$this->coreRegistry->register('mageplaza_blog_author', $author);
		return $author;
	}
}
