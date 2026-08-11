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

namespace Mageplaza\Blog\Block;

use Magento\Framework\View\Element\Template;

/**
 * Ad-hoc block used by PartialRenderer to render a shared template through
 * Magento's own template engine instead of a hand-rolled `include`.
 *
 * @package Mageplaza\Blog\Block
 */
class Partial extends Template
{
    /**
     * @var Template|null
     */
    private $callerBlock;

    /**
     * @param Template $callerBlock
     *
     * @return $this
     */
    public function setCallerBlock(Template $callerBlock)
    {
        $this->callerBlock = $callerBlock;

        return $this;
    }

    /**
     * Proxy methods not defined here (e.g. Frontend::resizeImage()) to the caller block,
     * so the shared partial can keep calling $block->someCallerMethod() like before.
     *
     * @param string $method
     * @param array $args
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if ($this->callerBlock !== null && method_exists($this->callerBlock, $method)) {
            return call_user_func_array([$this->callerBlock, $method], $args);
        }

        return parent::__call($method, $args);
    }
}
