<?php
/**
 * Created by PhpStorm.
 * User: HoangKuty
 * Date: 4/8/2017
 * Time: 2:21 PM
 */

namespace Mageplaza\Blog\Model;

use \Magento\Framework\Model\AbstractModel;

/**
 * Class Author
 * @package Mageplaza\Blog\Model
 */
class Author extends AbstractModel
{
    const CACHE_TAG = 'mageplaza_blog_author';
    protected function _construct()
    {
        $this->_init('Mageplaza\Blog\Model\ResourceModel\Author');
    }

	/**
	 * @return array
	 */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

	/**
	 * @return array
	 */
    public function getDefaultValues()
    {
        $values = [];

        return $values;
    }
}
