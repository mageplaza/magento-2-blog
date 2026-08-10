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

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Throwable;

/**
 * Renders a shared partial template in an isolated scope.
 *
 * Replaces plain `include $partial` inside templates: the partial no longer sees
 * the caller's whole variable scope, only `$block`, `$escaper` and the variables
 * explicitly passed in.
 *
 * @package Mageplaza\Blog\ViewModel
 */
class PartialRenderer implements ArgumentInterface
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Escaper $escaper
     */
    public function __construct(Escaper $escaper)
    {
        $this->escaper = $escaper;
    }

    /**
     * Render a resolved template file with an explicit variable set.
     *
     * @param AbstractBlock $block Caller block, exposed to the partial as $block
     * @param string $file Absolute path from $block->getTemplateFile()
     * @param array $vars Variables exposed to the partial
     *
     * @return string
     */
    public function render(AbstractBlock $block, $file, array $vars = [])
    {
        if (!$file || !is_file($file)) {
            return '';
        }

        $escaper = $this->escaper;

        // Bind only what the partial is allowed to see. EXTR_SKIP keeps the
        // caller from overwriting $block, $escaper or the local bookkeeping vars.
        $render = static function () use ($block, $escaper, $file, $vars) {
            extract($vars, EXTR_SKIP);

            ob_start();
            try {
                include $file;

                return (string) ob_get_clean();
            } catch (Throwable $e) {
                ob_end_clean();

                throw $e;
            }
        };

        return $render();
    }
}
