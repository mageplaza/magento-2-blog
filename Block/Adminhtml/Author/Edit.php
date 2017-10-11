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

namespace Mageplaza\Blog\Block\Adminhtml\Author;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;

/**
 * Class Edit
 * @package Mageplaza\Blog\Block\Adminhtml\Author
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
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
        Context $context,
        Registry $coreRegistry,
        array $data = []
    )
    {
        $this->coreRegistry = $coreRegistry;

        parent::__construct($context, $data);
    }

    /**
     * Initialize Post edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId   = 'user_id';
        $this->_blockGroup = 'Mageplaza_Blog';
        $this->_controller = 'adminhtml_author';

        parent::_construct();

        $this->buttonList->add(
            'save-and-continue',
            [
                'label'          => __('Save Change'),
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

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('delete');
    }

    /**
     * Retrieve text for header element depending on loaded Post
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var \Mageplaza\Blog\Model\Post $post */
        $author = $this->coreRegistry->registry('mageplaza_blog_author');
        if ($author->getId()) {
            return __("Edit Author '%1'", $this->escapeHtml($author->getName()));
        }

        return __('New Author');
    }
}
