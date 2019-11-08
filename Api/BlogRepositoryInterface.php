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

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

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
     * Create Post
     *
     * @param \Mageplaza\Blog\Api\Data\PostInterface $post
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface
     * @throws \Exception
     */
    public function createPost($post);

    /**
     * Delete Post
     *
     * @param string $postId
     *
     * @return string
     */
    public function deletePost($postId);

    /**
     * @param string $postId
     * @param \Mageplaza\Blog\Api\Data\PostInterface $post
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function updatePost($postId, $post);

    /**
     * Get Tag List
     *
     * @return \Mageplaza\Blog\Api\Data\TagInterface[]
     */
    public function getTagList();

    /**
     * Create Post
     *
     * @param \Mageplaza\Blog\Api\Data\TagInterface $tag
     *
     * @return \Mageplaza\Blog\Api\Data\TagInterface
     * @throws \Exception
     */
    public function createTag($tag);

    /**
     * Delete Tag
     *
     * @param string $tagId
     *
     * @return string
     */
    public function deleteTag($tagId);

    /**
     * @param string $tagId
     * @param \Mageplaza\Blog\Api\Data\TagInterface $tag
     *
     * @return \Mageplaza\Blog\Api\Data\TagInterface
     */
    public function updateTag($tagId, $tag);

    /**
     * Get Topic List
     *
     * @return \Mageplaza\Blog\Api\Data\TopicInterface[]
     */
    public function getTopicList();

    /**
     * Create Topic
     *
     * @param \Mageplaza\Blog\Api\Data\TopicInterface $topic
     *
     * @return \Mageplaza\Blog\Api\Data\TopicInterface
     * @throws \Exception
     */
    public function createTopic($topic);

    /**
     * Delete Topic
     *
     * @param string $topicId
     *
     * @return string
     */
    public function deleteTopic($topicId);

    /**
     * @param string $topicId
     * @param \Mageplaza\Blog\Api\Data\TopicInterface $topic
     *
     * @return \Mageplaza\Blog\Api\Data\TopicInterface
     */
    public function updateTopic($topicId, $topic);

    /**
     * Get Category List
     *
     * @return \Mageplaza\Blog\Api\Data\CategoryInterface[]
     */
    public function getCategoryList();

    /**
     * Create Category
     *
     * @param \Mageplaza\Blog\Api\Data\CategoryInterface $category
     *
     * @return \Mageplaza\Blog\Api\Data\CategoryInterface
     * @throws \Exception
     */
    public function createCategory($category);

    /**
     * Delete Category
     *
     * @param string $categoryId
     *
     * @return string
     */
    public function deleteCategory($categoryId);

    /**
     * @param string $categoryId
     * @param \Mageplaza\Blog\Api\Data\CategoryInterface $category
     *
     * @return \Mageplaza\Blog\Api\Data\CategoryInterface
     */
    public function updateCategory($categoryId, $category);

    /**
     * Get Author List
     *
     * @return \Mageplaza\Blog\Api\Data\AuthorInterface[]
     */
    public function getAuthorList();

    /**
     * Create Author
     *
     * @param \Mageplaza\Blog\Api\Data\AuthorInterface $author
     *
     * @return \Mageplaza\Blog\Api\Data\AuthorInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createAuthor($author);

    /**
     * Delete Author
     *
     * @param string $authorId
     *
     * @return string
     */
    public function deleteAuthor($authorId);

    /**
     * @param string $authorId
     * @param \Mageplaza\Blog\Api\Data\AuthorInterface $author
     *
     * @return \Mageplaza\Blog\Api\Data\AuthorInterface
     */
    public function updateAuthor($authorId, $author);
}
