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
namespace Mageplaza\Blog\Block\Adminhtml\Topic\Edit\Tab;

class Topic extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
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
     * @var \Mageplaza\Blog\Model\Topic\Source\MetaRobots
     */
    protected $metaRobotsOptions;

    /**
     * constructor
     *
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
     * @param \Mageplaza\Blog\Model\Topic\Source\MetaRobots $metaRobotsOptions
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \Mageplaza\Blog\Model\Topic\Source\MetaRobots $metaRobotsOptions,
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
        /** @var \Mageplaza\Blog\Model\Topic $topic */
        $topic = $this->_coreRegistry->registry('mageplaza_blog_topic');
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('topic_');
        $form->setFieldNameSuffix('topic');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('Topic Information'),
                'class'  => 'fieldset-wide'
            ]
        );
        if ($topic->getId()) {
            $fieldset->addField(
                'topic_id',
                'hidden',
                ['name' => 'topic_id']
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
            'url_key',
            'text',
            [
                'name'  => 'url_key',
                'label' => __('URL Key'),
                'title' => __('URL Key'),
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

        $topicData = $this->_session->getData('mageplaza_blog_topic_data', true);
        if ($topicData) {
            $topic->addData($topicData);
        } else {
            if (!$topic->getId()) {
                $topic->addData($topic->getDefaultValues());
            }
        }
        $form->addValues($topic->getData());
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
        return __('Topic');
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
