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

namespace Mageplaza\Blog\Model;

use \Magento\Framework\Model\AbstractModel;

/**
 * Class Author
 * @package Mageplaza\Blog\Model
 */
class Author extends AbstractModel
{
    /**
     * @inheritdoc
     */
    const CACHE_TAG = 'mageplaza_blog_author';

    /**
     * Constructor
     */
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
}
