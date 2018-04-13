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

namespace Mageplaza\Blog\Block\Adminhtml\Import\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\Blog\Helper\Image as ImageHelper;
use Mageplaza\Core\Block\Adminhtml\Renderer\Image;
use Mageplaza\Blog\Model\Config\Source\Import\Type;

/**
 * Class Form
 * @package Mageplaza\Blog\Block\Adminhtml\Import\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
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
     * @var Type
     */
    protected $importType;

    /**
     * Form constructor.
     * @param Config $wysiwygConfig
     * @param Store $systemStore
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param ImageHelper $imageHelper
     * @param Type $importType
     * @param array $data
     */
    public function __construct(
        Config $wysiwygConfig,
        Store $systemStore,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        ImageHelper $imageHelper,
        Type $importType,
        array $data = []
    )
    {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->systemStore = $systemStore;
        $this->imageHelper = $imageHelper;
        $this->importType = $importType;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @inheritdoc
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => '',
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ],
            ]
        );
        $form->setFieldNameSuffix('import');

        $fieldsets['base'] = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Import Settings')
            ]
        );

        $fieldsets['base']->addField('import_type', 'select', [
                'name' => 'import_type',
                'label' => __('Import Type'),
                'title' => __('Import Type'),
                'values' => $this->importType->toOptionArray(),
                'required' => true,
                'onchange' => 'mpBlogImport.initImportFieldsSet();'
            ]
        );

        $fieldsetList = $this->importType->toOptionArray();
        array_shift($fieldsetList);

        foreach ($fieldsetList as $item){

            $fieldsets[$item["value"]] = $form->addFieldset(
                $item["value"] . '_fieldset',
                [
                    'legend' => $item["label"]->getText(). __(' Import'),
                    'class' => 'no-display'
                ]
            );
            $fieldsets[$item["value"]]->addField(
                $item["value"].'_import_name',
                'hidden',
                [
                    'name' => 'import_name',
                    'value' => $item["label"]->getText()
                ]);

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_db_name',
                'text',
                [
                    'name' => 'db_name',
                    'title' => __('Database Name'),
                    'label' => __('Database Name'),
                    'required' => true,
                    'class' => $item["value"],
                    'note' => __('Your SQL database name')
                ]
            );

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_user_name',
                'text',
                [
                    'name' => 'user_name',
                    'title' => __('Database User Name'),
                    'label' => __('Database User Name'),
                    'required' => true,
                    'class' => $item["value"],
                    'note' => __('Your SQL database User name')
                ]
            );

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_db_password',
                'text',
                [
                    'name' => 'db_password',
                    'title' => __('Database Password'),
                    'label' => __('Database Password'),
                    'required' => true,
                    'class' => $item["value"],
                    'note' => __('Your SQL database Password')
                ]
            );

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_db_host',
                'text',
                [
                    'name' => 'db_host',
                    'title' => __('Database Password'),
                    'label' => __('Database Password'),
                    'required' => true,
                    'class' => $item["value"],
                    'value' => 'localhost',
                    'note' => __('Your SQL database Hostname')
                ]
            );

            $fieldsets[$item["value"]]->addField(
                $item["value"].'_table_prefix',
                'text',
                [
                    'name' => 'table_prefix',
                    'title' => __('Table Prefix'),
                    'label' => __('Table Prefix'),
                    'required' => true,
                    'class' => $item["value"],
                    'value' => '_wp',
                    'note' => __('Your table prefix name')
                ]
            );
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
