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

namespace Mageplaza\Blog\Block\Adminhtml\Author\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\Blog\Block\Adminhtml\Renderer\Image;
use Mageplaza\Blog\Helper\Image as ImageHelper;

/**
 * Class Author
 * @package Mageplaza\Blog\Block\Adminhtml\Author\Edit\Tab
 */
class Author extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    public $systemStore;

    /**
     * @var Config
     */
    public $wysiwygConfig;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * Author constructor.
     *
     * @param Config $wysiwygConfig
     * @param Store $systemStore
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ImageHelper $imageHelper
     * @param array $data
     */
    public function __construct(
        Config $wysiwygConfig,
        Store $systemStore,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->systemStore = $systemStore;
        $this->imageHelper = $imageHelper;

        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\Blog\Model\Author $author */
        $author = $this->_coreRegistry->registry('mageplaza_blog_author');

        /** @var Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('author_');
        $form->setFieldNameSuffix('author');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Author Information'),
            'class'  => 'fieldset-wide'
        ]);

        if ($author->getId()) {
            $fieldset->addField('user_id', 'hidden', ['name' => 'user_id']);
        }

        $fieldset->addField('name', 'text', [
            'name'     => 'name',
            'label'    => __('Display Name'),
            'title'    => __('Display Name'),
            'required' => true,
            'note'     => __('This name will be displayed on frontend')
        ]);
        $fieldset->addField('short_description', 'editor', [
            'name'   => 'short_description',
            'label'  => __('Short Description'),
            'title'  => __('Short Description'),
            'note'   => __('Short Description'),
            'config' => $this->wysiwygConfig->getConfig()
        ]);
        $fieldset->addField('image', Image::class, [
            'name'  => 'image',
            'label' => __('Avatar'),
            'title' => __('Avatar'),
            'path'  => $this->imageHelper->getBaseMediaPath(ImageHelper::TEMPLATE_MEDIA_TYPE_AUTH)
        ]);
        $fieldset->addField('url_key', 'text', [
            'name'  => 'url_key',
            'label' => __('URL Key'),
            'title' => __('URL Key')
        ]);
        $fieldset->addField('facebook_link', 'text', [
            'name'  => 'facebook_link',
            'label' => __('Facebook'),
            'title' => __('Facebook'),
            'note'  => __('Facebook URL'),
        ]);
        $fieldset->addField('twitter_link', 'text', [
            'name'  => 'twitter_link',
            'label' => __('Twitter'),
            'title' => __('Twitter'),
            'note'  => __('Twitter URL'),
        ]);

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
