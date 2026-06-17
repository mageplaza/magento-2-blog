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

namespace Mageplaza\Blog\Block\Author;

use Magento\Framework\DataObject\IdentityInterface;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\AuthorFactory;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;

/**
 * Class ListPost
 * @package Mageplaza\Blog\Block\Author
 */
class ListPost extends \Mageplaza\Blog\Block\ListPost
{
    /**
     * @var AuthorFactory
     */
    protected $_author;

    /**
     * Override this function to apply collection for each type
     *
     * @return Collection
     */
    protected function getCollection()
    {
        if ($author = $this->getAuthor()) {
            return $this->helperData->getPostCollection(Data::TYPE_AUTHOR, $author->getId());
        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function getAuthor()
    {
        if (!$this->_author) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $author = $this->helperData->getObjectByParam($id, null, Data::TYPE_AUTHOR);
                if ($author && $author->getId()) {
                    $this->_author = $author;
                }
            }
        }

        return $this->_author;
    }

    /**
     * Add the current author tag so editing the author in admin purges this page
     * (Author model implements IdentityInterface). Base adds the global blog-post tag.
     *
     * @return string[]
     */
    public function getIdentities()
    {
        $identities = parent::getIdentities();

        $author = $this->getAuthor();
        if ($author instanceof IdentityInterface && $author->getId()) {
            $identities = array_merge($identities, $author->getIdentities());
        }

        return array_unique($identities);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $author = $this->getAuthor();
            if ($author) {
                $breadcrumbs->addCrumb($author->getUrlKey(), [
                    'label' => __('Author'),
                    'title' => __('Author')
                ]);
            }
        }
    }

    /**
     * @param bool $meta
     *
     * @return array
     */
    public function getBlogTitle($meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);
        $author = $this->getAuthor();
        if (!$author) {
            return $blogTitle;
        }

        if ($meta) {
            array_push($blogTitle, ucfirst($author->getName()));

            return $blogTitle;
        }

        return ucfirst($author->getName());
    }
}
