<?php
/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 *                     @category  Mageplaza
 *                     @package   Mageplaza_Blog
 *                     @copyright Copyright (c) 2016
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Block\Adminhtml\Category\Edit\Tab;

class Category extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Wysiwyg config
     *
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * Country options
     *
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $booleanOptions;

    /**
     * Meta Robots options
     *
     * @var \Mageplaza\Blog\Model\Category\Source\MetaRobots
     */
    protected $metaRobotsOptions;

    /**
     * constructor
     *
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \Mageplaza\Blog\Model\Category\Source\MetaRobots $metaRobotsOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \Mageplaza\Blog\Model\Category\Source\MetaRobots $metaRobotsOptions,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
    
        $this->wysiwygConfig     = $wysiwygConfig;
        $this->booleanOptions    = $booleanOptions;
        $this->metaRobotsOptions = $metaRobotsOptions;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Mageplaza\Blog\Model\Category $category */
        $category = $this->_coreRegistry->registry('mageplaza_blog_category');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('category_');
        $form->setFieldNameSuffix('category');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Category Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        if (!$category->getId()) {
            // path
            if ($this->getRequest()->getParam('parent')) {
                $fieldset->addField(
                    'path',
                    'hidden',
                    ['name' => 'path', 'value' => $this->getRequest()->getParam('parent')]
                );
            } else {
                $fieldset->addField('path', 'hidden', ['name' => 'path', 'value' => 1]);
            }
        } else {
            $fieldset->addField(
                'category_id',
                'hidden',
                ['name' => 'category_id', 'value' => $category->getId()]
            );
            $fieldset->addField(
                'path',
                'hidden',
                ['name' => 'path', 'value' => $category->getPath()]
            );
        }
        $fieldset->addField(
            'name',
            'text',
            [
                'name'  => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
            ]
        );
        $fieldset->addField(
            'description',
            'editor',
            [
                'name'  => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'config'    => $this->wysiwygConfig->getConfig()
            ]
        );
        $fieldset->addField(
            'url_key',
            'text',
            [
                'name'  => 'url_key',
                'label' => __('URL Key'),
                'title' => __('URL Key'),
            ]
        );
        $fieldset->addField(
            'enabled',
            'select',
            [
                'name'  => 'enabled',
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'values' => $this->booleanOptions->toOptionArray(),
            ]
        );
        $fieldset->addField(
            'meta_title',
            'text',
            [
                'name'  => 'meta_title',
                'label' => __('Meta Title'),
                'title' => __('Meta Title'),
            ]
        );
        $fieldset->addField(
            'meta_description',
            'textarea',
            [
                'name'  => 'meta_description',
                'label' => __('Meta Description'),
                'title' => __('Meta Description'),
            ]
        );
        $fieldset->addField(
            'meta_keywords',
            'textarea',
            [
                'name'  => 'meta_keywords',
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords'),
            ]
        );
        $fieldset->addField(
            'meta_robots',
            'select',
            [
                'name'  => 'meta_robots',
                'label' => __('Meta Robots'),
                'title' => __('Meta Robots'),
                'values' => array_merge(['' => ''], $this->metaRobotsOptions->toOptionArray()),
            ]
        );

        $categoryData = $this->_session->getData('mageplaza_blog_category_data', true);
        if ($categoryData) {
            $category->addData($categoryData);
        } else {
            if (!$category->getId()) {
                $category->addData($category->getDefaultValues());
            }
        }
        $form->addValues($category->getData());
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
        return __('Category');
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
