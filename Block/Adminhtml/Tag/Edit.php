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
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Blog\Block\Adminhtml\Tag;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
	public $coreRegistry;

    /**
     * constructor
     *
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
    
        $this->coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize Tag edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'tag_id';
        $this->_blockGroup = 'Mageplaza_Blog';
        $this->_controller = 'adminhtml_tag';
        parent::_construct();
        $this->buttonList->update('save', 'label', __('Save Tag'));
        $this->buttonList->add(
            'save-and-continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
        $this->buttonList->update('delete', 'label', __('Delete Tag'));
    }
    /**
     * Retrieve text for header element depending on loaded Tag
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \Mageplaza\Blog\Model\Tag $tag */
        $tag = $this->coreRegistry->registry('mageplaza_blog_tag');
        if ($tag->getId()) {
            return __("Edit Tag '%1'", $this->escapeHtml($tag->getName()));
        }
        return __('New Tag');
    }
}
