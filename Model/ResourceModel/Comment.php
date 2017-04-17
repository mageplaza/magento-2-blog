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
namespace Mageplaza\Blog\Model\ResourceModel;

class Comment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
	/**
	 * Date model
	 *
	 * @var \Magento\Framework\Stdlib\DateTime\DateTime
	 */
	public $date;

	/**
	 * Event Manager
	 *
	 * @var \Magento\Framework\Event\ManagerInterface
	 */
	public $eventManager;

	/**
	 * constructor
	 *
	 * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
	 * @param \Magento\Framework\Event\ManagerInterface $eventManager
	 * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
	 */
	public function __construct(
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Framework\Event\ManagerInterface $eventManager,
		\Magento\Framework\Model\ResourceModel\Db\Context $context
	)
	{
		$this->date         = $date;
		$this->eventManager = $eventManager;
		parent::__construct($context);
	}

	/**
	 * Initialize resource model
	 *
	 * @return void
	 */
	protected function _construct()
	{
		$this->_init('mageplaza_blog_comment', 'comment_id');
	}
}