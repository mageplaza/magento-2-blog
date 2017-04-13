<?php
/**
 * Created by PhpStorm.
 * User: HoangKuty
 * Date: 4/8/2017
 * Time: 2:23 PM
 */
namespace Mageplaza\Blog\Model\ResourceModel;

use \Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Author extends AbstractDb
{
	protected $_isPkAutoIncrement = false;
	protected function _construct()
	{
		$this->_init('mageplaza_blog_author','user_id');
	}
}