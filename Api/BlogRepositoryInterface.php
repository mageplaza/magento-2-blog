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

namespace Mageplaza\Blog\Api;

/**
 * Class PostInterface
 * @package Mageplaza\Blog\Api
 */
interface BlogRepositoryInterface
{
    /**
     * Get Post List
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface[]
     */
    public function getPostList();

    /**
     * Get Tag List
     *
     * @return \Mageplaza\Blog\Api\Data\TagInterface[]
     */
    public function getTagList();

    /**
     * Get Topic List
     *
     * @return \Mageplaza\Blog\Api\Data\TopicInterface[]
     */
    public function getTopicList();

    /**
     * Get Category List
     *
     * @return \Mageplaza\Blog\Api\Data\CategoryInterface[]
     */
    public function getCategoryList();

    /**
     * Get Author List
     *
     * @return \Mageplaza\Blog\Api\Data\AuthorInterface[]
     */
    public function getAuthorList();
}
