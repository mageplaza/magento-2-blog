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

namespace Mageplaza\Blog\Block\Adminhtml\Comment;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\Comment;
use Mageplaza\Blog\Model\Post;

/**
 * Class Edit
 * @package Mageplaza\Blog\Block\Adminhtml\Comment
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * constructor
     *
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
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
        $this->_blockGroup = 'Mageplaza_Blog';
        $this->_controller = 'adminhtml_comment';

        parent::_construct();

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
        $this->buttonList->update('save', 'label', 'Save Comment');
        $this->buttonList->remove('reset');
    }

    /**
     * Retrieve text for header element depending on loaded Post
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var Comment $comment */
        $comment = $this->coreRegistry->registry('mageplaza_blog_comment');
        if ($comment->getId()) {
            return __("Edit Comment");
        }

        return __('New Comment');
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        /** @var Post $post */
        $comment = $this->coreRegistry->registry('mageplaza_blog_comment');
        if ($id = $comment->getId()) {
            return $this->getUrl('*/*/save', ['id' => $id]);
        }

        return parent::getFormActionUrl();
    }
}
