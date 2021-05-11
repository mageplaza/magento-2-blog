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

namespace Mageplaza\Blog\Api\Data;

/**
 * Interface PostInterface
 * @package Mageplaza\Blog\Api\Data
 */
interface PostInterface
{
    /**
     * Constants used as data array keys
     */
    const POST_ID           = 'post_id';
    const NAME              = 'name';
    const SHORT_DESCRIPTION = 'short_description';
    const POST_CONTENT      = 'post_content';
    const STORE_IDS         = 'store_ids';
    const IMAGE             = 'image';
    const ENABLED           = 'enabled';
    const URL_KEY           = 'url_key';
    const IN_RSS            = 'in_rss';
    const ALLOW_COMMENT     = 'allow_comment';
    const META_TITLE        = 'meta_title';
    const META_DESCRIPTION  = 'meta_description';
    const META_KEYWORDS     = 'meta_keywords';
    const META_ROBOTS       = 'meta_robots';
    const UPDATED_AT        = 'updated_at';
    const CREATED_AT        = 'created_at';
    const AUTHOR_ID         = 'author_id';
    const MODIFIER_ID       = 'modifier_id';
    const PUBLISH_DATE      = 'publish_date';
    const IMPORT_SOURCE     = 'import_source';
    const LAYOUT            = 'layout';
    const CATEGORY_IDS      = 'category_ids';
    const TAG_IDS           = 'tag_ids';
    const TOPIC_IDS         = 'topic_ids';
    const AUTHOR_URL        = 'author_url';
    const AUTHOR_NAME       = 'author_name';
    const VIEW_TRAFFIC      = 'view_traffic';

    const ATTRIBUTES = [
        self::POST_ID,
        self::NAME,
        self::SHORT_DESCRIPTION,
        self::POST_CONTENT,
        self::STORE_IDS,
        self::IMAGE,
        self::ENABLED,
        self::URL_KEY,
        self::IN_RSS,
        self::ALLOW_COMMENT,
        self::META_TITLE,
        self::META_DESCRIPTION,
        self::META_KEYWORDS,
        self::META_ROBOTS,
        self::AUTHOR_ID,
        self::MODIFIER_ID,
        self::PUBLISH_DATE,
        self::IMPORT_SOURCE,
        self::LAYOUT,
        self::CATEGORY_IDS,
        self::TAG_IDS,
        self::TOPIC_IDS,
        self::AUTHOR_NAME,
        self::AUTHOR_URL,
        self::VIEW_TRAFFIC
    ];

    /**
     * Get Post id
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set Post id
     *
     * @param int $id
     *
     * @return $this
     */
    public function setId($id);

    /**
     * Get Post Name
     *
     * @return string/null
     */
    public function getName();

    /**
     * Set Post Name
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name);

    /**
     * Get Post Short Description
     *
     * @return string/null
     */
    public function getShortDescription();

    /**
     * Set Post Short Description
     *
     * @param string $content
     *
     * @return $this
     */
    public function setShortDescription($content);

    /**
     * Get Post Content
     *
     * @return string/null
     */
    public function getPostContent();

    /**
     * Set Post Content
     *
     * @param string $content
     *
     * @return $this
     */
    public function setPostContent($content);

    /**
     * Get Post Store Id
     *
     * @return int/null
     */
    public function getStoreIds();

    /**
     * Set Post Store Id
     *
     * @param int $storeId
     *
     * @return $this
     */
    public function setStoreIds($storeId);

    /**
     * Get Post Image
     *
     * @return string/null
     */
    public function getImage();

    /**
     * Set Post Image
     *
     * @param string $content
     *
     * @return $this
     */
    public function setImage($content);

    /**
     * Get Post Enabled
     *
     * @return int/null
     */
    public function getEnabled();

    /**
     * Set Post Enabled
     *
     * @param int $enabled
     *
     * @return $this
     */
    public function setEnabled($enabled);

    /**
     * Get Post Url Key
     *
     * @return string/null
     */
    public function getUrlKey();

    /**
     * Set Post Url Key
     *
     * @param string $url
     *
     * @return $this
     */
    public function setUrlKey($url);

    /**
     * Get Post In RSS
     *
     * @return int/null
     */
    public function getInRss();

    /**
     * Set Post Enabled
     *
     * @param int $inRss
     *
     * @return $this
     */
    public function setInRss($inRss);

    /**
     * Get Post Allow Comment
     *
     * @return int/null
     */
    public function getAllowComment();

    /**
     * Set Post Allow Comment
     *
     * @param int $allow
     *
     * @return $this
     */
    public function setAllowComment($allow);

    /**
     * Get Post Meta Title
     *
     * @return string/null
     */
    public function getMetaTitle();

    /**
     * Set Post Meta Title
     *
     * @param string $meta
     *
     * @return $this
     */
    public function setMetaTitle($meta);

    /**
     * Get Post Meta Description
     *
     * @return string/null
     */
    public function getMetaDescription();

    /**
     * Set Post Meta Description
     *
     * @param string $meta
     *
     * @return $this
     */
    public function setMetaDescription($meta);

    /**
     * Get Post Meta Keywords
     *
     * @return string/null
     */
    public function getMetaKeywords();

    /**
     * Set Post Meta Keywords
     *
     * @param string $meta
     *
     * @return $this
     */
    public function setMetaKeywords($meta);

    /**
     * Get Post Meta Robots
     *
     * @return string/null
     */
    public function getMetaRobots();

    /**
     * Set Post Meta Robots
     *
     * @param string $meta
     *
     * @return $this
     */
    public function setMetaRobots($meta);

    /**
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * @param string $createdAt
     *
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get Post updated date
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set Post updated date
     *
     * @param string $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get Post Author Id
     *
     * @return int/null
     */
    public function getAuthorId();

    /**
     * Set Post Store Id
     *
     * @param int $authorId
     *
     * @return $this
     */
    public function setAuthorId($authorId);

    /**
     * Get Post Modifier Id
     *
     * @return int/null
     */
    public function getModifierId();

    /**
     * Set Post Modifier Id
     *
     * @param int $id
     *
     * @return $this
     */
    public function setModifierId($id);

    /**
     * get Post Publish date
     *
     * @return string|null
     */
    public function getPublishDate();

    /**
     * Set Post Publish date
     *
     * @param string $publishDate
     *
     * @return $this
     */
    public function setPublishDate($publishDate);

    /**
     * @return string|null
     */
    public function getImportSource();

    /**
     * @param string $importSource
     *
     * @return $this
     */
    public function setImportSource($importSource);

    /**
     * @return string|null
     */
    public function getLayout();

    /**
     * @param string $layout
     *
     * @return $this
     */
    public function setLayout($layout);

    /**
     * @return int[]|null
     */
    public function getCategoryIds();

    /**
     * @param int[] $array
     *
     * @return $this
     */
    public function setCategoryIds($array);

    /**
     * @return int[]|null
     */
    public function getTagIds();

    /**
     * @param int[] $array
     *
     * @return $this
     */
    public function setTagIds($array);

    /**
     * @return int[]|null
     */
    public function getTopicIds();

    /**
     * @param int[] $array
     *
     * @return $this
     */
    public function setTopicIds($array);

    /**
     * @return string|null
     */
    public function getAuthorName();

    /**
     * @return string|null
     */
    public function getAuthorUrl();

    /**
     * @return int
     */
    public function getViewTraffic();
}
