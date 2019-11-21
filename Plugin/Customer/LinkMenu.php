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

use Mageplaza\Blog\Helper\Data;
use Magento\Framework\View\Element\Html\Links;

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
     * @param \Magento\Framework\View\Element\Html\Link[] $links
     *
     * @return mixed
     */
    public function afterGetLinks(
        Links $subject,
        $links
    ) {
        if ($this->helper->isEnabled() && !$this->helper->getConfigGeneral('customer_approve')) {
            if ($links) {
                foreach ($links as $key => $link) {
                    if ($link->getPath() === 'mpblog/author/signup') {
                        $author = $this->helper->getCurrentAuthor();
                        if (!$author->getId()) {
                            unset($links[$key]);
                        }
                    }
                }
            }
        }

        return $links;
    }
}
