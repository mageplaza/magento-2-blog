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

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mageplaza\Blog\Api\Data\AuthorInterface;
use Mageplaza\Blog\Api\Data\CategoryInterface;
use Mageplaza\Blog\Api\Data\CommentInterface;
use Mageplaza\Blog\Api\Data\PostInterface;
use Mageplaza\Blog\Api\Data\SearchResult\CategorySearchResultInterface;
use Mageplaza\Blog\Api\Data\SearchResult\CommentSearchResultInterface;
use Mageplaza\Blog\Api\Data\SearchResult\PostSearchResultInterface;
use Mageplaza\Blog\Api\Data\SearchResult\TagSearchResultInterface;
use Mageplaza\Blog\Api\Data\SearchResult\TopicSearchResultInterface;
use Mageplaza\Blog\Api\Data\TagInterface;
use Mageplaza\Blog\Api\Data\TopicInterface;

/**
 * Class PostInterface
 * @package Mageplaza\Blog\Api
 */
interface BlogRepositoryInterface
{
    /**
     * @return PostInterface[]
     */
    public function getAllPost();

    /**
     * @param string $postId
     *
     * @return PostInterface
     */
    public function getPostView($postId);

    /**
     * @param string $authorName
     *
     * @return PostInterface[]
     */
    public function getPostViewByAuthorName($authorName);

    /**
     * @param string $authorId
     *
     * @return PostInterface[]
     */
    public function getPostViewByAuthorId($authorId);

    /**
     * @param string $postId
     *
     * @return CommentInterface[]
     */
    public function getPostComment($postId);

    /**
     * Get All Comment
     *
     * @return CommentInterface[]
     */
    public function getAllComment();

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return CommentSearchResultInterface
     */
    public function getCommentList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param string $commentId
     *
     * @return CommentInterface
     */
    public function getCommentView($commentId);

    /**
     * @param string $postId
     *
     * @return string
     */
    public function getPostLike($postId);

    /**
     * @param string $tagName
     *
     * @return PostInterface[]
     */
    public function getPostByTagName($tagName);

    /**
     * @param string $postId
     *
     * @return ProductInterface[]
     */
    public function getProductByPost($postId);

    /**
     * @param string $postId
     *
     * @return PostInterface[]
     * @throws LocalizedException
     */
    public function getPostRelated($postId);

    /**
     * @param string $postId
     * @param CommentInterface $commentData
     *
     * @return CommentInterface
     * @throws Exception
     */
    public function addCommentInPost($postId, $commentData);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return PostSearchResultInterface
     * @throws NoSuchEntityException
     */
    public function getPostList(SearchCriteriaInterface $searchCriteria);

    /**
     * Create Post
     *
     * @param PostInterface $post
     *
     * @return PostInterface
     * @throws Exception
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
     * @param PostInterface $post
     *
     * @return PostInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function updatePost($postId, $post);

    /**
     * Get All Tag
     *
     * @return TagInterface[]
     */
    public function getAllTag();

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return TagSearchResultInterface
     */
    public function getTagList(SearchCriteriaInterface $searchCriteria);

    /**
     * Create Post
     *
     * @param TagInterface $tag
     *
     * @return TagInterface
     * @throws Exception
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
     *
     * @return TagInterface
     */
    public function getTagView($tagId);

    /**
     * @param string $tagId
     * @param TagInterface $tag
     *
     * @return TagInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function updateTag($tagId, $tag);

    /**
     * Get Topic List
     *
     * @return TopicInterface[]
     */
    public function getAllTopic();

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return TopicSearchResultInterface
     */
    public function getTopicList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param string $topicId
     *
     * @return TagInterface
     */
    public function getTopicView($topicId);

    /**
     * @param string $topicId
     *
     * @return PostInterface[]
     */
    public function getPostsByTopic($topicId);

    /**
     * Create Topic
     *
     * @param TopicInterface $topic
     *
     * @return TopicInterface
     * @throws Exception
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
     * @param TopicInterface $topic
     *
     * @return TopicInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function updateTopic($topicId, $topic);

    /**
     * Get All Category
     *
     * @return CategoryInterface[]
     */
    public function getAllCategory();

    /**
     * Get Category List
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return CategorySearchResultInterface
     */
    public function getCategoryList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param string $categoryId
     *
     * @return CategoryInterface
     */
    public function getCategoryView($categoryId);

    /**
     * @param string $categoryId
     *
     * @return PostInterface[]
     */
    public function getPostsByCategoryId($categoryId);

    /**
     * @param string $categoryKey
     *
     * @return PostInterface[]
     */
    public function getPostsByCategory($categoryKey);

    /**
     * @param string $postId
     *
     * @return CategoryInterface[]
     */
    public function getCategoriesByPostId($postId);

    /**
     * Create Category
     *
     * @param CategoryInterface $category
     *
     * @return CategoryInterface
     * @throws Exception
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
     * @param CategoryInterface $category
     *
     * @return CategoryInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function updateCategory($categoryId, $category);

    /**
     * Get Author List
     *
     * @return AuthorInterface[]
     */
    public function getAuthorList();

    /**
     * Create Author
     *
     * @param AuthorInterface $author
     *
     * @return AuthorInterface
     * @throws NoSuchEntityException
     * @throws LocalizedException
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
     * @param AuthorInterface $author
     *
     * @return AuthorInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function updateAuthor($authorId, $author);
}
