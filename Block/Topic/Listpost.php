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

namespace Mageplaza\Blog\Block\Topic;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Model\ResourceModel\Post\Collection;
use Mageplaza\Blog\Model\TopicFactory;

/**
 * Class Listpost
 * @package Mageplaza\Blog\Block\Topic
 */
class Listpost extends \Mageplaza\Blog\Block\Listpost
{
    /**
     * @var TopicFactory
     */
    protected $_topic;

    /**
     * Override this function to apply collection for each type
     *
     * @return Collection
     * @throws NoSuchEntityException
     */
    protected function getCollection()
    {
        if ($topic = $this->getBlogObject()) {
            return $this->helperData->getPostCollection(Data::TYPE_TOPIC, $topic->getId());
        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function getBlogObject()
    {
        if (!$this->_topic) {
            $id = $this->getRequest()->getParam('id');

            if ($id) {
                $topic = $this->helperData->getObjectByParam($id, null, Data::TYPE_TOPIC);
                if ($topic && $topic->getId()) {
                    $this->_topic = $topic;
                }
            }
        }

        return $this->_topic;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $topic     = $this->getBlogObject();
            $topicName = preg_replace('/[^A-Za-z0-9\-]/', ' ', $topic->getName());
            if ($topic) {
                $breadcrumbs->addCrumb($topic->getUrlKey(), [
                    'label' => __($topicName),
                    'title' => __($topicName)
                ]);
            }
        }
    }

    /**
     * @param bool $meta
     *
     * @return array|Phrase|string
     */
    public function getBlogTitle($meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);
        $topic     = $this->getBlogObject();
        if (!$topic) {
            return $blogTitle;
        }

        if ($meta) {
            if ($topic->getMetaTitle()) {
                array_push($blogTitle, $topic->getMetaTitle());
            } else {
                array_push($blogTitle, ucfirst($topic->getName()));
            }

            return $blogTitle;
        }

        return ucfirst($topic->getName());
    }
}
