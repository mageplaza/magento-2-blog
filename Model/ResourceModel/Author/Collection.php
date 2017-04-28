<?php
/**
 * Created by PhpStorm.
 * User: HoangKuty
 * Date: 4/8/2017
 * Time: 2:25 PM
 */

namespace Mageplaza\Blog\Model\ResourceModel\Author;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
	protected $_idFieldName = 'user_id';
	protected function _construct()
	{
		$this->_init('Mageplaza\Blog\Model\Author','Mageplaza\Blog\Model\ResourceModel\Author');
	}
}