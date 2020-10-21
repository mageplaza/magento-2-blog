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
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\System\Store;
use Mageplaza\Blog\Block\Adminhtml\Renderer\Image;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Image as ImageHelper;
use Mageplaza\Blog\Model\Config\Source\AuthorStatus;

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
     * @var AuthorStatus
     */
    protected $authorStatus;

    /**
     * @var Data
     */
    protected $_helperData;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Author constructor.
     *
     * @param Config $wysiwygConfig
     * @param Store $systemStore
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ImageHelper $imageHelper
     * @param AuthorStatus $authorStatus
     * @param Data $helperData
     * @param CustomerRepositoryInterface $customerRepository
     * @param array $data
     */
    public function __construct(
        Config $wysiwygConfig,
        Store $systemStore,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ImageHelper $imageHelper,
        AuthorStatus $authorStatus,
        Data $helperData,
        CustomerRepositoryInterface $customerRepository,
        array $data = []
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->systemStore = $systemStore;
        $this->imageHelper = $imageHelper;
        $this->authorStatus = $authorStatus;
        $this->_helperData = $helperData;
        $this->customerRepository = $customerRepository;

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
            'class' => 'fieldset-wide'
        ]);

        if ($author->getId()) {
            $fieldset->addField('user_id', 'hidden', ['name' => 'user_id']);
        }

        if ($this->checkCustomerId($author->getCustomerId())) {
            $fieldset->addField('customer_id', 'hidden', [
                'name' => 'customer_id',
                'value' => $author->getCustomerId()
            ]);
            $customer = $this->customerRepository->getById($author->getCustomerId());

            $fieldset->addField('customer', 'label', [
                'name' => 'customer',
                'label' => __('Customer'),
                'title' => __('Customer'),
                'value' => $customer->getFirstname() . ' ' . $customer->getLastname()
            ]);
        } else {
            $fieldset->addField('customer', 'text', [
                'name' => 'customer',
                'label' => __('Customer'),
                'title' => __('Customer')
            ])->setAfterElementHtml(
                '<div id="customer-grid" style="display:none"></div>
                <script type="text/x-magento-init">
                    {
                        "#author_customer": {
                            "Mageplaza_Blog/js/view/author":{
                                "url": "' . $this->getAjaxUrl() . '"
                            }
                        }
                    }
                </script>'
            );
            $fieldset->addField('customer_id', 'hidden', [
                'name' => 'customer_id',
                'value' => 0
            ]);
        }

        $fieldset->addField('name', 'text', [
            'name' => 'name',
            'label' => __('Display Name'),
            'title' => __('Display Name'),
            'required' => true,
            'note' => __('This name will be displayed on frontend')
        ]);

        $fieldset->addField('status', 'select', [
            'name' => 'status',
            'label' => __('Status'),
            'title' => __('Status'),
            'values' => $this->authorStatus->toArray()
        ]);

        $fieldset->addField('type', 'hidden', [
            'name' => 'type',
            'value' => 0
        ]);

        $fieldset->addField('short_description', 'editor', [
            'name' => 'short_description',
            'label' => __('Short Description'),
            'title' => __('Short Description'),
            'note' => __('Short Description'),
            'config' => $this->wysiwygConfig->getConfig([
                'add_variables' => false,
                'add_widgets' => false,
                'add_directives' => true
            ])
        ]);

        $fieldset->addField('image', Image::class, [
            'name' => 'image',
            'label' => __('Avatar'),
            'title' => __('Avatar'),
            'path' => $this->imageHelper->getBaseMediaPath(ImageHelper::TEMPLATE_MEDIA_TYPE_AUTH)
        ]);

        $fieldset->addField('url_key', 'text', [
            'name' => 'url_key',
            'label' => __('URL Key'),
            'title' => __('URL Key')
        ]);

        $authorUrlFormat = $this->_storeManager->getDefaultStoreView()->getBaseUrl(UrlInterface::URL_TYPE_LINK)
            . 'blog/author/';
        $urlSuffix = $this->_helperData->getUrlSuffix();

        $fieldset->addField(
            'full_url',
            'label',
            [
                'label' => __('Full URL'),
                'value' => $author->getUrlKey() ? $authorUrlFormat . $author->getUrlKey() . $urlSuffix : ''
            ]
        )->setAfterElementHtml(
            "<script>
                require(['jquery'], function($){
                    $('#author_url_key').on('keyup', function() {
                        var url = '" . $authorUrlFormat . "'+$(this).val()+'" . $urlSuffix . "';

                        if ($(this).val() === ''){
                            url = '';
                        }
                        $('.field-full_url .control-value').html(url);
                    });
                });
            </script>"
        );

        $fieldset->addField('facebook_link', 'text', [
            'name' => 'facebook_link',
            'label' => __('Facebook'),
            'title' => __('Facebook'),
            'note' => __('Facebook URL'),
            'required' => false,
            'class' => 'validate-url'
        ]);

        $fieldset->addField('twitter_link', 'text', [
            'name' => 'twitter_link',
            'label' => __('Twitter'),
            'title' => __('Twitter'),
            'note' => __('Twitter URL'),
            'required' => false,
            'class' => 'validate-url'
        ]);

        $form->addValues($author->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @param $customerId
     *
     * @return bool
     * @throws LocalizedException
     */
    public function checkCustomerId($customerId)
    {
        try {
            if ($this->customerRepository->getById($customerId)) {
                return true;
            }
        } catch (NoSuchEntityException $noSuchEntityException) {
            return false;
        }

        return false;
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

    /**
     * Get transaction grid url
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('mageplaza_blog/author/customergrid');
    }
}
