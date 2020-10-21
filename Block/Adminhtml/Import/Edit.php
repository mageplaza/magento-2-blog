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

namespace Mageplaza\Blog\Block\Adminhtml\Import;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;

/**
 * Class Edit
 * @package Mageplaza\Blog\Block\Adminhtml\Import
 */
class Edit extends Container
{
    /**
     * Internal constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->buttonList->remove('back');
        $this->buttonList->remove('reset');
        $this->buttonList->remove('save');

        $this->buttonList->add(
            'check-connection',
            [
                'label' => __('Check Connection'),
                'class' => 'primary',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'target' => '#edit_form',

                        ]
                    ]
                ],
                'onclick' => 'mpBlogImport.initImportCheckConnection();'
            ],
            -100
        );
        $this->_objectId = 'import_id';
        $this->_blockGroup = 'Mageplaza_Blog';
        $this->_controller = 'adminhtml_import';
    }

    /**
     * Get header text
     *
     * @return Phrase
     */
    public function getHeaderText()
    {
        return __('Import Setting');
    }
}
