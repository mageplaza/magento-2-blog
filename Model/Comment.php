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

namespace Mageplaza\Blog\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\ResourceModel\Comment\CollectionFactory as CommentCollectionFactory;
use Mageplaza\Blog\Model\ResourceModel\Post\CollectionFactory;

/**
 * Class Comment
 * @package Mageplaza\Blog\Model
 */
class Comment extends AbstractModel
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

    /**
     * @var string
     */
    protected $_idFieldName = 'comment_id';

    /**
     * Post Collection Factory
     *
     * @var CollectionFactory
     */
    public $postCollectionFactory;

    /**
     * @var CommentCollectionFactory
     */
    public $commentCollectionFactory;

    /**
     * Comment constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param CollectionFactory $postCollectionFactory
     * @param CommentCollectionFactory $commentCollectionFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CollectionFactory $postCollectionFactory,
        CommentCollectionFactory $commentCollectionFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->postCollectionFactory = $postCollectionFactory;
        $this->commentCollectionFactory = $commentCollectionFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Comment::class);
    }

    /**
     * @inheritdoc
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
