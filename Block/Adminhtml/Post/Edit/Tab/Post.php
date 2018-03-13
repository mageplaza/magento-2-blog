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
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Config\Model\Config\Source\Design\Robots;
use Magento\Config\Model\Config\Source\Enabledisable;
use Magento\Config\Model\Config\Source\Yesno;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\Blog\Helper\Image;

/**
 * Class Post
 * @package Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab
 */
class Post extends Generic implements TabInterface
{
    /**
     * Wysiwyg config
     *
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    public $wysiwygConfig;

    /**
     * Country options
     *
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    public $booleanOptions;

    /**
     * @var \Magento\Config\Model\Config\Source\Enabledisable
     */
    protected $enabledisable;

    /**
     * @var \Magento\Config\Model\Config\Source\Design\Robots
     */
    public $metaRobotsOptions;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $systemStore;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Mageplaza\Blog\Helper\Image
     */
    protected $imageHelper;

    /**
     * Post constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \Magento\Config\Model\Config\Source\Enabledisable $enableDisable
     * @param \Magento\Config\Model\Config\Source\Design\Robots $metaRobotsOptions
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Mageplaza\Blog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Session $authSession,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Yesno $booleanOptions,
        Enabledisable $enableDisable,
        Robots $metaRobotsOptions,
        Store $systemStore,
        Image $imageHelper,
        array $data = []
    )
    {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->booleanOptions = $booleanOptions;
        $this->enabledisable = $enableDisable;
        $this->metaRobotsOptions = $metaRobotsOptions;
        $this->systemStore = $systemStore;
        $this->authSession = $authSession;
        $this->imageHelper = $imageHelper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\Blog\Model\Post $post */
        $post = $this->_coreRegistry->registry('mageplaza_blog_post');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('post_');
        $form->setFieldNameSuffix('post');

        $fieldset = $form->addFieldset('base_fieldset', [
                'legend' => __('Post Information'),
                'class' => 'fieldset-wide'
            ]
        );

        $fieldset->addField('author_id', 'hidden', ['name' => 'author_id']);

        $fieldset->addField('name', 'text', [
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            ]
        );
        $fieldset->addField('enabled', 'select', [
                'name' => 'enabled',
                'label' => __('Status'),
                'title' => __('Status'),
                'values' => $this->enabledisable->toOptionArray()
            ]
        );
        if (!$post->hasData('enabled')) {
            $post->setEnabled(1);
        }

        $fieldset->addField('short_description', 'textarea', [
                'name' => 'short_description',
                'label' => __('Short Description'),
                'title' => __('Short Description')
            ]
        );
        $fieldset->addField('post_content', 'editor', [
                'name' => 'post_content',
                'label' => __('Content'),
                'title' => __('Content'),
                'config' => $this->wysiwygConfig->getConfig(['add_variables' => false, 'add_widgets' => false, 'add_directives' => true])
            ]
        );

        if (!$this->_storeManager->isSingleStoreMode()) {
            /** @var \Magento\Framework\Data\Form\Element\Renderer\RendererInterface $rendererBlock */
            $rendererBlock = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
            $fieldset->addField('store_ids', 'multiselect', [
                'name' => 'store_ids',
                'label' => __('Store Views'),
                'title' => __('Store Views'),
                'values' => $this->systemStore->getStoreValuesForForm(false, true)
            ])->setRenderer($rendererBlock);

            if (!$post->hasData('store_ids')) {
                $post->setStoreIds(0);
            }
        } else {
            $fieldset->addField('store_ids', 'hidden', [
                'name' => 'store_ids',
                'value' => $this->_storeManager->getStore()->getId()
            ]);
        }

        $fieldset->addField('image', \Mageplaza\Core\Block\Adminhtml\Renderer\Image::class, [
                'name' => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
                'path' => $this->imageHelper->getBaseMediaPath(Image::TEMPLATE_MEDIA_TYPE_POST)
            ]
        );
        $fieldset->addField('categories_ids', '\Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Category', [
                'name' => 'categories_ids',
                'label' => __('Categories'),
                'title' => __('Categories'),
            ]
        );
        if (!$post->getCategoriesIds()) {
            $post->setCategoriesIds($post->getCategoryIds());
        }

        $fieldset->addField('topics_ids', '\Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Topic', [
                'name' => 'topics_ids',
                'label' => __('Topics'),
                'title' => __('Topics'),
            ]
        );
        if (!$post->getTopicsIds()) {
            $post->setTopicsIds($post->getTopicIds());
        }

        $fieldset->addField('tags_ids', '\Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\Tag', [
                'name' => 'tags_ids',
                'label' => __('Tags'),
                'title' => __('Tags'),
            ]
        );
        if (!$post->getTagsIds()) {
            $post->setTagsIds($post->getTagIds());
        }

        $fieldset->addField('in_rss', 'select', [
                'name' => 'in_rss',
                'label' => __('In RSS'),
                'title' => __('In RSS'),
                'values' => $this->booleanOptions->toOptionArray(),
            ]
        );
        $fieldset->addField('allow_comment', 'select', [
                'name' => 'allow_comment',
                'label' => __('Allow Comment'),
                'title' => __('Allow Comment'),
                'values' => $this->booleanOptions->toOptionArray(),
            ]
        );
        $fieldset->addField('publish_date', 'date', array(
                'name' => 'publish_date',
                'label' => __('Publish Date'),
                'title' => __('Publish Date'),
                'date_format' => $this->_localeDate->getDateFormat(\IntlDateFormatter::SHORT),
                'time_format' => $this->_localeDate->getTimeFormat(\IntlDateFormatter::SHORT),
                'timezone' => false
            )
        );

        $seoFieldset = $form->addFieldset('seo_fieldset', [
                'legend' => __('Search Engine Optimization'),
                'class' => 'fieldset-wide'
            ]
        );
        $seoFieldset->addField('url_key', 'text', [
                'name' => 'url_key',
                'label' => __('URL Key'),
                'title' => __('URL Key')
            ]
        );
        $seoFieldset->addField('meta_title', 'text', [
                'name' => 'meta_title',
                'label' => __('Meta Title'),
                'title' => __('Meta Title')
            ]
        );
        $seoFieldset->addField('meta_description', 'textarea', [
                'name' => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description')
            ]
        );
        $seoFieldset->addField('meta_keywords', 'textarea', [
                'name' => 'meta_keywords',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords')
            ]
        );
        $seoFieldset->addField('meta_robots', 'select', [
                'name' => 'meta_robots',
                'label' => __('Meta Robots'),
                'title' => __('Meta Robots'),
                'values' => $this->metaRobotsOptions->toOptionArray()
            ]
        );

        if (!$post->getId()) {
            $post->addData([
                'allow_comment' => 1,
                'meta_title' => $this->_scopeConfig->getValue('blog/seo/meta_title'),
                'meta_description' => $this->_scopeConfig->getValue('blog/seo/meta_description'),
                'meta_keywords' => $this->_scopeConfig->getValue('blog/seo/meta_keywords'),
                'meta_robots' => $this->_scopeConfig->getValue('blog/seo/meta_robots'),
            ]);
        }

        if ($post->getData('publish_date')) {
            $date = $post->getData('publish_date');
            $date = new \DateTime($date, new \DateTimeZone('UTC'));
            $date->setTimezone(new \DateTimeZone($this->_localeDate->getConfigTimezone()));
            $post->setData('publish_date', $date);
        }

        $form->addValues($post->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Post');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
