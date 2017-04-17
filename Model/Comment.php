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
namespace Mageplaza\Blog\Model;

class Comment extends \Magento\Framework\Model\AbstractModel
{
	/**
	 * Cache tag
	 *
	 * @var string
	 */
	const CACHE_TAG = 'mageplaza_blog_comment';

	/**
	 * Cache tag
	 *
	 * @var string
	 */
	protected $_cacheTag = 'mageplaza_blog_comment';

	/**
	 * Event prefix
	 *
	 * @var string
	 */
	protected $_eventPrefix = 'mageplaza_blog_comment';

	protected $_idFieldName = 'comment_id';

	/**
	 * Post Collection Factory
	 * @type \Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory
	 */
	public $postCollectionFactory;

	public function __construct(
		\Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory $postCollectionFactory,
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
		array $data = []
	)
	{
		$this->postCollectionFactory     = $postCollectionFactory;
		parent::__construct($context, $registry, $resource, $resourceCollection, $data);
	}

	/**
	 * Initialize resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('Mageplaza\Blog\Model\ResourceModel\Comment');
	}

	public function getIdentities()
	{
		return [self::CACHE_TAG . '_' . $this->getId()];
	}
}