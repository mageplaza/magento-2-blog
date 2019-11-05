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

use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
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
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * BlogRepository constructor.
     *
     * @param Data $helperData
     * @param RequestInterface $request
     */
    public function __construct(
        Data $helperData,
        RequestInterface $request,
        DateTime $date
    ) {
        $this->_helperData = $helperData;
        $this->request     = $request;
        $this->date        = $date;
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

    /**
     * @return \Mageplaza\Blog\Api\Data\PostInterface[]|string|null
     */
    public function createPost()
    {
        $data = $this->request->getParams();

        if ($this->checkPostData($data)) {
            $this->prepareData($data);

            $post = $this->_helperData->getFactoryByType()->create()->addData($data);

            try{
                $post->save();
                return 'Success';
            }catch (Exception $exception){
                return $exception->getMessage();
            }
        }
        return null;
    }

    public function deletePost($postId)
    {
        $post = $this->_helperData->getFactoryByType()->create()->load($postId);

        if ($post){
            $post->delete();

            return 'Delete Success';
        }

        return null;
    }

    /**
     * @param array $data
     */
    protected function prepareData(&$data)
    {
        if (!empty($data['categories_ids'])) {
            $data['categories_ids'] = explode(',', $data['categories_ids']);
        }
        if (!empty($data['tags_ids'])) {
            $data['tags_ids'] = explode(',', $data['tags_ids']);
        }
        if (!empty($data['topics_ids'])) {
            $data['topics_ids'] = explode(',', $data['topics_ids']);
        }
        if (empty($data['enabled'])) {
            $data['enabled'] = 0;
        }
        if (empty($data['allow_comment'])) {
            $data['allow_comment'] = 0;
        }
        if (empty($data['store_ids'])) {
            $data['store_ids'] = 0;
        }
        if (empty($data['in_rss'])) {
            $data['in_rss'] = 0;
        }
        if (empty($data['meta_robots'])) {
            $data['meta_robots'] = 'INDEX,FOLLOW';
        }
        if (empty($data['layout'])) {
            $data['layout'] = 'empty';
        }
        $data['created_at'] = $this->date->date();

        if (empty($data['publish_date'])) {
            $data['publish_date'] = $this->date->date();
        }
    }

    /**
     * @param $data
     *
     * @return bool
     */
    protected function checkPostData($data)
    {
        if (empty($data['name']) || empty($data['author_id']) || !$this->checkAuthor($data['author_id'])) {
            return false;
        }

        if (!empty($data['categories_ids'])) {
            $collection = $this->_helperData->getFactoryByType('category')->create()->getCollection();
            foreach (explode(',', $data['categories_ids']) as $id) {
                if ($collection->addFieldToFilter('category_id', $id)->count() < 1) {
                    return false;
                }
            }
        }

        if (!empty($data['tags_ids'])) {
            $collection = $this->_helperData->getFactoryByType('tag')->create()->getCollection();
            foreach (explode(',', $data['tags_ids']) as $id) {
                if ($collection->addFieldToFilter('tag_id', $id)->count() < 1) {
                    return false;
                }
            }
        }

        if (!empty($data['topics_ids'])) {
            $collection = $this->_helperData->getFactoryByType('topic')->create()->getCollection();
            foreach (explode(',', $data['topics_ids']) as $id) {
                if ($collection->addFieldToFilter('topic_id', $id)->count() < 1) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @param $authorId
     *
     * @return bool
     */
    protected function checkAuthor($authorId)
    {
        $collection = $this->_helperData->getFactoryByType('author')->create()->getCollection()
            ->addFieldToFilter('user_id', $authorId);

        return $collection->count() > 0 ? true : false;
    }
}