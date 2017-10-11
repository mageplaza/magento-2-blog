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
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Adminhtml\Tag;

use Magento\Backend\Block\Widget\Form\Container;

/**
 * Class Edit
 * @package Mageplaza\Blog\Block\Adminhtml\Tag
 */
class Edit extends Container
{
    /**
     * @var \Magento\Framework\Registry
     */
    public $coreRegistry;

    /**
     * Edit constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    )
    {
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
        $this->_blockGroup = 'Mageplaza_Blog';
        $this->_controller = 'adminhtml_tag';

        parent::_construct();

        $this->buttonList->add(
            'save-and-continue',
            [
                'label'          => __('Save and Continue Edit'),
                'class'          => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event'  => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ],
            -100
        );
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
