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

namespace Mageplaza\Blog\ViewModel;

use Mageplaza\Blog\Block\Partial;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\Element\Template;

/**
 * Renders a shared partial template through Magento's own block/template engine
 * (Magento\Framework\View\TemplateEngine\Php), instead of a hand-rolled `include`
 * inside the module's own code.
 *
 * @package Mageplaza\Blog\ViewModel
 */
class PartialRenderer implements ArgumentInterface
{
    /**
     * Render a resolved template file with an explicit variable set.
     *
     * @param Template $block Caller block, reachable from the partial via $block->callerMethod()
     * @param string $file Absolute path from $block->getTemplateFile()
     * @param array $vars Variables exposed to the partial as plain $varName
     *
     * @return string
     */
    public function render(Template $block, $file, array $vars = [])
    {
        if (!$file || !is_file($file)) {
            return '';
        }

        /** @var Partial $partial */
        $partial = $block->getLayout()->createBlock(Partial::class);
        $partial->setCallerBlock($block);
        $partial->assign($vars);

        // fetchView() takes the already-resolved absolute path directly and validates
        // it via Template\File\Validator; setTemplate()+toHtml() would instead treat
        // $file as a "Module::path" identifier and re-resolve it, which is wrong here.
        return $partial->fetchView($file);
    }
}
