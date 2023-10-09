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

namespace Mageplaza\Blog\Plugin\Customer;

use Magento\Framework\View\Element\Html\Link;
use Magento\Framework\View\Element\Html\Links;
use Mageplaza\Blog\Helper\Data;

/**
 * Class LinkMenu
 * @package Mageplaza\Blog\Plugin\Customer
 */
class LinkMenu
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * Topmenu constructor.
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param Links $subject
     * @param Link[] $links
     *
     * @return mixed
     */
    public function afterGetLinks(
        Links $subject,
        $links
    ) {
        if ($links && $this->helper->isEnabled() && !$this->helper->getConfigGeneral('customer_approve')) {
            foreach ($links as $key => $link) {
                if ($link->getPath() === 'mpblog/author/signup') {
                    $this->helper->setCustomerContextId();
                    $author = $this->helper->getCurrentAuthor();
                    if ($author === null || !$author->getId()) {
                        unset($links[$key]);
                    }
                }
            }
        }

        return $links;
    }
}
