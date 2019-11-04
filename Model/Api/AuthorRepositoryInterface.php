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

namespace Mageplaza\Blog\Model\Api;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Blog\Api\Post\AuthorInterface;
use Mageplaza\Blog\Helper\Data;

/**
 * Class AuthorRepositoryInterface
 * @package Mageplaza\Blog\Model\Api
 */
class AuthorRepositoryInterface implements AuthorInterface
{
    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * PostRepositoryInterface constructor.
     *
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    )
    {
        $this->_helperData = $helperData;
    }


    /**
     * @return DataObject[]|PostInterface[]
     * @throws NoSuchEntityException
     */
    public function getPostList()
    {
        $collection = $this->_helperData->getPostCollection();

        return $collection->getItems();
    }
}