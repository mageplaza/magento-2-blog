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

namespace Mageplaza\Blog\Block\Adminhtml\Post;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\Post;

/**
 * Class Edit
 * @package Mageplaza\Blog\Block\Adminhtml\Post
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
        $this->_controller = 'adminhtml_post';

        parent::_construct();

        $post = $this->coreRegistry->registry('mageplaza_blog_post');
        if ($post->getId() && !$post->getDuplicate()) {
            $this->buttonList->add(
                'duplicate',
                [
                    'label'   => __('Duplicate'),
                    'class'   => 'duplicate',
                    'onclick' => sprintf("location.href = '%s';", $this->getDuplicateUrl()),
                ],
                -101
            );
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
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded Post
     *
     * @return string
     */
    public function getHeaderText()
    {
        /** @var Post $post */
        $post = $this->coreRegistry->registry('mageplaza_blog_post');
        if ($post->getId() && $post->getDuplicate()) {
            return __("Edit Post '%1'", $this->escapeHtml($post->getName()));
        }

        return __('New Post');
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        /** @var Post $post */
        $post = $this->coreRegistry->registry('mageplaza_blog_post');
        if ($post->getId()) {
            if ($post->getDuplicate()) {
                $ar = [
                    'id'        => $post->getId(),
                    'duplicate' => $post->getDuplicate()
                ];
            } else {
                $ar = ['id' => $post->getId()];
            }

            return $this->getUrl('*/*/save', $ar);
        }

        return parent::getFormActionUrl();
    }

    /**
     * @return string
     */
    protected function getDuplicateUrl()
    {
        $post = $this->coreRegistry->registry('mageplaza_blog_post');

        return $this->getUrl('*/*/duplicate', ['id' => $post->getId(), 'duplicate' => true]);
    }
}
