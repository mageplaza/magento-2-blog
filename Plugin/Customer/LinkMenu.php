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

use Magento\Framework\Module\Manager as ModuleManager;
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
     * @var ModuleManager
     */
    protected $_moduleManager;

    /**
     * @var Data
     */
    protected $_helper;

    /**
     * Topmenu constructor.
     *
     * @param Data $helper
     * @param ModuleManager $moduleManager
     */
    public function __construct(
        ModuleManager $moduleManager,
        Data $helper
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_helper        = $helper;
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
        if ($this->_moduleManager->isEnabled('Mageplaza_BlogPro') && $this->_helper->getPostViewPageConfig('enable_to_save')) {
            return $links;
        } else {
            $links = $this->unsetLinks($links);
        }

        return $links;
    }

    /**
     * @param $links
     *
     * @return mixed
     */
    protected function unsetLinks($links)
    {
        if ($links && !$this->_helper->getConfigGeneral('customer_approve')) {
            foreach ($links as $key => $link) {
                if ($link->getPath() === 'mpblog/author/signup') {
                    $this->_helper->setCustomerContextId();
                    $author = $this->_helper->getCurrentAuthor();
                    if ($author === null || !$author->getId()) {
                        unset($links[$key]);
                    }
                }
            }
        }

        return $links;
    }

}
