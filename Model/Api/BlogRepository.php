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
use Mageplaza\Blog\Api\BlogRepositoryInterface;
use Mageplaza\Blog\Helper\Data;

/**
 * Class PostRepositoryInterface
 * @package Mageplaza\Blog\Model\Api
 */
class BlogRepository implements BlogRepositoryInterface
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
     * @return DataObject[]|BlogRepositoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getPostList()
    {
        $collection = $this->_helperData->getPostCollection();

        return $collection->getItems();
    }

    /**
     * @return DataObject[]|BlogRepositoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getTagList()
    {
        $collection = $this->_helperData->getFactoryByType('tag')->create()->getCollection();

        return $collection->getItems();
    }

    /**
     * @return DataObject[]|BlogRepositoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getTopicList()
    {
        $collection = $this->_helperData->getFactoryByType('topic')->create()->getCollection();

        return $collection->getItems();
    }

    /**
     * @return DataObject[]|BlogRepositoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getAuthorList()
    {
        $collection = $this->_helperData->getFactoryByType('author')->create()->getCollection();

        return $collection->getItems();
    }

    /**
     * @return DataObject[]|BlogRepositoryInterface[]
     * @throws NoSuchEntityException
     */
    public function getCategoryList()
    {
        $collection = $this->_helperData->getFactoryByType('category')->create()->getCollection();

        return $collection->getItems();
    }
}