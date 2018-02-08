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

namespace Mageplaza\Blog\Block\Adminhtml\Author\Edit\Tab;

use Mageplaza\Core\Block\Adminhtml\Renderer\Image;

/**
 * Class Author
 * @package Mageplaza\Blog\Block\Adminhtml\Author\Edit\Tab
 */
class Author extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    public $systemStore;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    public $wysiwygConfig;

    /**
     * @var \Mageplaza\Blog\Helper\Image
     */
    protected $imageHelper;

    /**
     * Author constructor.
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Mageplaza\Blog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Mageplaza\Blog\Helper\Image $imageHelper,
        array $data = []
    )
    {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->systemStore = $systemStore;
        $this->imageHelper = $imageHelper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\Blog\Model\Author $author */
        $author = $this->_coreRegistry->registry('mageplaza_blog_author');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('author_');
        $form->setFieldNameSuffix('author');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Author Information'),
                'class' => 'fieldset-wide'
            ]
        );

        if ($author->getId()) {
            $fieldset->addField('user_id', 'hidden', ['name' => 'user_id']);
        }

        $fieldset->addField('name', 'text', [
                'name' => 'name',
                'label' => __('Display Name'),
                'title' => __('Display Name'),
                'required' => true,
                'note' => __('This name will be displayed on frontend')
            ]
        );

        $fieldset->addField('short_description', 'editor', [
                'name' => 'short_description',
                'label' => __('Short Description'),
                'title' => __('Short Description'),
                'note' => __('Short Description'),
                'config' => $this->wysiwygConfig->getConfig()
            ]
        );

        $fieldset->addField('image', Image::class, [
                'name' => 'image',
                'label' => __('Avatar'),
                'title' => __('Avatar'),
                'path' => $this->imageHelper->getBaseMediaPath(\Mageplaza\Blog\Helper\Image::TEMPLATE_MEDIA_TYPE_AUTH)
            ]
        );

        $fieldset->addField('url_key', 'text', [
                'name' => 'url_key',
                'label' => __('URL Key'),
                'title' => __('URL Key')
            ]
        );

        $fieldset->addField('facebook_link', 'text', [
                'name' => 'facebook_link',
                'label' => __('Facebook'),
                'title' => __('Facebook'),
                'note' => __('Facebook URL'),
            ]
        );

        $fieldset->addField('twitter_link', 'text', [
                'name' => 'twitter_link',
                'label' => __('Twitter'),
                'title' => __('Twitter'),
                'note' => __('Twitter URL'),
            ]
        );

        $form->addValues($author->getData());
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
        return __('Author Info');
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
