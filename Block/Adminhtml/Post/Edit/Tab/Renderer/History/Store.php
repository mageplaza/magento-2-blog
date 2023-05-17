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
 * @package     Mageplaza_LayeredNavigationUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer\History;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Store\Model\System\Store as SystemStore;

/**
 * Class Name
 * @package Mageplaza\LayeredNavigationUltimate\Block\Adminhtml\Grid\Renderer
 */
class Store extends AbstractRenderer
{
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * System store
     *
     * @var SystemStore
     */
    protected $systemStore;

    /**
     * Store constructor.
     *
     * @param SystemStore $systemStore
     * @param Escaper $escaper
     */
    public function __construct(
        SystemStore $systemStore,
        Escaper $escaper
    ) {
        $this->systemStore = $systemStore;
        $this->escaper     = $escaper;
    }

    /**
     * Renders grid column
     *
     * @param DataObject $row
     *
     * @return  string
     */
    public function render(DataObject $row)
    {
        if ($row) {
            $store  = '';
            $store .= $this->prepareItem($row->getData());

            return $store;
        }

        return '';
    }

    /**
     * @param array $item
     * @return \Magento\Framework\Phrase|string
     */
    protected function prepareItem(array $item)
    {
        $content    = '';
        $origStores = [];
        $items      = explode(",", $item['store_ids']);

        foreach ($items as $storeId) {
            $origStores[] = $storeId;
        }

        if (in_array(0, $origStores)) {
            return __('All Store Views');
        }

        $data = $this->systemStore->getStoresStructure(false, $origStores);
        foreach ($data as $website) {
            $content .= "<b>" . $website['label'] . "</b><br/>";
            foreach ($website['children'] as $group) {
                $content .= str_repeat('&nbsp;', 3) . "<b>" . $this->escaper->escapeHtml($group['label']) . "</b><br/>";
                foreach ($group['children'] as $store) {
                    $content .= str_repeat('&nbsp;', 6) . $this->escaper->escapeHtml($store['label']) . "<br/>";
                }
            }
        }

        return $content;
    }
}
