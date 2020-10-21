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

namespace Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\History;

use Exception;
use Magento\Backend\Block\Context;
use Magento\Framework\DataObject;
use Magento\Framework\Json\EncoderInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Action
 * @package Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Action
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Action constructor.
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $jsonEncoder, $data);
    }

    /**
     * @param DataObject $row
     *
     * @return string
     */
    public function render(DataObject $row)
    {
        $actions[] = [
            'url' =>
                $this->getUrl('*/history/edit', [
                    'id' => $row->getId(),
                    'post_id' => $row->getPostId(),
                    'history' => true
                ]),
            'popup' => false,
            'caption' => __('Edit'),
        ];
        try {
            $actions[] = [
                'url' => $this->_storeManager->getStore()->getBaseUrl()
                    . 'mpblog/post/preview?id=' . $row->getPostId() . '&historyId=' . $row->getId(),
                'popup' => true,
                'caption' => __('Preview'),
            ];
        } catch (Exception $exception) {
            $actions[] = [];
        }
        $actions[] = [
            'url' =>
                $this->getUrl('*/history/restore', [
                    'id' => $row->getId(),
                    'post_id' => $row->getPostId()
                ]),
            'popup' => false,
            'caption' => __('Restore'),
            'confirm' => 'Are you sure you want to do this?'
        ];

        $actions[] = [
            'url' =>
                $this->getUrl('*/history/delete', [
                    'id' => $row->getId(),
                    'post_id' => $row->getPostId()
                ]),
            'popup' => false,
            'caption' => __('Delete'),
            'confirm' => 'Are you sure you want to do this?'
        ];

        $this->getColumn()->setActions($actions);

        return parent::render($row);
    }
}
