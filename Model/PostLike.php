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

use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class PostLike
 * @package Mageplaza\Blog\Model
 */
class PostLike extends AbstractModel implements IdentityInterface
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_blog_post_like';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_blog_post_like';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_blog_post_like';

    /**
     * @var string
     */
    protected $_idFieldName = 'like_id';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\PostLike::class);
    }

    /**
     * Invalidate the post page this vote belongs to.
     *
     * The like_id tag is never emitted by any block, so cleaning it invalidated
     * nothing and the cached page kept serving a stale counter. The post page
     * tag is the one present in X-Magento-Tags, so it also reaches Varnish and
     * Fastly through the standard clean_cache_by_tags event.
     *
     * @return array
     */
    public function getIdentities()
    {
        $postId = (int) $this->getPostId();

        return $postId ? [Post::CACHE_TAG . '_' . $postId] : [];
    }
}
