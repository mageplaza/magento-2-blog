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
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PostInterface
 * @package Mageplaza\Blog\Api
 */
interface BlogRepositoryInterface
{
    /**
     * @return \Mageplaza\Blog\Api\Data\PostInterface[]
     */
    public function getAllPost();

    /**
     * @return \Mageplaza\Blog\Api\Data\MonthlyArchiveInterface[]
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function getMonthlyArchive();

    /**
     * @param string $monthly
     * @param string $year
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface[]
     */
    public function getPostMonthlyArchive($monthly, $year);

    /**
     * @param string $postId
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface
     * @throws Exception
     */
    public function getPostView($postId);

    /**
     * @param string $authorName
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface[]
     */
    public function getPostViewByAuthorName($authorName);

    /**
     * @param string $authorId
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface[]
     */
    public function getPostViewByAuthorId($authorId);

    /**
     * @param string $postId
     *
     * @return \Mageplaza\Blog\Api\Data\CommentInterface[]
     */
    public function getPostComment($postId);

    /**
     * Get All Comment
     *
     * @return \Mageplaza\Blog\Api\Data\CommentInterface[]
     */
    public function getAllComment();

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Mageplaza\Blog\Api\Data\SearchResult\CommentSearchResultInterface
     */
    public function getCommentList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param string $commentId
     *
     * @return \Mageplaza\Blog\Api\Data\CommentInterface
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
     * @return \Mageplaza\Blog\Api\Data\PostInterface[]
     */
    public function getPostByTagName($tagName);

    /**
     * @param string $postId
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface[]
     */
    public function getProductByPost($postId);

    /**
     * @param string $postId
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface[]
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPostRelated($postId);

    /**
     * @param string $postId
     * @param \Mageplaza\Blog\Api\Data\CommentInterface $commentData
     *
     * @return \Mageplaza\Blog\Api\Data\CommentInterface
     * @throws Exception
     */
    public function addCommentInPost($postId, $commentData);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Mageplaza\Blog\Api\Data\SearchResult\PostSearchResultInterface
     * @throws NoSuchEntityException
     */
    public function getPostList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Create Post
     *
     * @param \Mageplaza\Blog\Api\Data\PostInterface $post
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface
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
     * @param \Mageplaza\Blog\Api\Data\PostInterface $post
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function updatePost($postId, $post);

    /**
     * Get All Tag
     *
     * @return \Mageplaza\Blog\Api\Data\TagInterface[]
     */
    public function getAllTag();

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Mageplaza\Blog\Api\Data\SearchResult\TagSearchResultInterface
     */
    public function getTagList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Create Post
     *
     * @param \Mageplaza\Blog\Api\Data\TagInterface $tag
     *
     * @return \Mageplaza\Blog\Api\Data\TagInterface
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
     * @return \Mageplaza\Blog\Api\Data\TagInterface
     */
    public function getTagView($tagId);

    /**
     * @param string $tagId
     * @param \Mageplaza\Blog\Api\Data\TagInterface $tag
     *
     * @return \Mageplaza\Blog\Api\Data\TagInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function updateTag($tagId, $tag);

    /**
     * Get Topic List
     *
     * @return \Mageplaza\Blog\Api\Data\TopicInterface[]
     */
    public function getAllTopic();

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Mageplaza\Blog\Api\Data\SearchResult\TopicSearchResultInterface
     */
    public function getTopicList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param string $topicId
     *
     * @return \Mageplaza\Blog\Api\Data\TagInterface
     */
    public function getTopicView($topicId);

    /**
     * @param string $topicId
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface[]
     */
    public function getPostsByTopic($topicId);

    /**
     * Create Topic
     *
     * @param \Mageplaza\Blog\Api\Data\TopicInterface $topic
     *
     * @return \Mageplaza\Blog\Api\Data\TopicInterface
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
     * @param \Mageplaza\Blog\Api\Data\TopicInterface $topic
     *
     * @return \Mageplaza\Blog\Api\Data\TopicInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function updateTopic($topicId, $topic);

    /**
     * Get All Category
     *
     * @return \Mageplaza\Blog\Api\Data\CategoryInterface[]
     */
    public function getAllCategory();

    /**
     * Get Category List
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Mageplaza\Blog\Api\Data\SearchResult\CategorySearchResultInterface
     */
    public function getCategoryList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param string $categoryId
     *
     * @return \Mageplaza\Blog\Api\Data\CategoryInterface
     */
    public function getCategoryView($categoryId);

    /**
     * @param string $categoryId
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface[]
     */
    public function getPostsByCategoryId($categoryId);

    /**
     * @param string $categoryKey
     *
     * @return \Mageplaza\Blog\Api\Data\PostInterface[]
     */
    public function getPostsByCategory($categoryKey);

    /**
     * @param string $postId
     *
     * @return \Mageplaza\Blog\Api\Data\CategoryInterface[]
     */
    public function getCategoriesByPostId($postId);

    /**
     * Create Category
     *
     * @param \Mageplaza\Blog\Api\Data\CategoryInterface $category
     *
     * @return \Mageplaza\Blog\Api\Data\CategoryInterface
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
     * @param \Mageplaza\Blog\Api\Data\CategoryInterface $category
     *
     * @return \Mageplaza\Blog\Api\Data\CategoryInterface
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
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
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function updateAuthor($authorId, $author);

    /**
     * @return \Mageplaza\Blog\Api\Data\BlogConfigInterface
     *
     * @throws InputException
     */
    public function getConfig();
}
