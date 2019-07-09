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

namespace Mageplaza\Blog\Block\Adminhtml\Category;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Context;

/**
 * Class Edit
 * @package Mageplaza\Blog\Block\Adminhtml\Category
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
     * Edit constructor.
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
     * prepare the form
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Mageplaza_Blog';
        $this->_controller = 'adminhtml_category';

        parent::_construct();

        /** @var \Mageplaza\Blog\Model\Category $category */
        $category = $this->coreRegistry->registry('category');

        if ($category->getId() && !$category->getDuplicate()) {
            $this->buttonList->add(
                'duplicate',
                [
                    'label' => __('Duplicate'),
                    'class' => 'duplicate',
                    'onclick' => sprintf("location.href = '%s';", $this->getDuplicateUrl()),
                ],
                -101
            );
        }

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('save');
    }

    /**
     * Get form action URL
     *
     * @return string
     */
    public function getFormActionUrl()
    {
        /** @var \Mageplaza\Blog\Model\Category $category */
        $category = $this->coreRegistry->registry('category');
        if ($category->getId()) {
            if ($category->getDuplicate()) {
                $ar = [
                    'id' => $category->getId(),
                    'duplicate' => $category->getDuplicate()
                ];
            } else {
                $ar = ['id' => $category->getId()];
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
        /** @var \Mageplaza\Blog\Model\Category $category */
        $category = $this->coreRegistry->registry('category');
        return $this->getUrl('*/*/duplicate', ['id' => $category->getId(), 'duplicate' => true]);
    }
}
