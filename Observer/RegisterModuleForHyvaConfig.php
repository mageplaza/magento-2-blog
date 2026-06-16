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
declare(strict_types=1);

namespace Mageplaza\Blog\Observer;

use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Component\ComponentRegistrarInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RegisterModuleForHyvaConfig implements ObserverInterface
{
    public function __construct(
        private readonly ComponentRegistrarInterface $componentRegistrar
    ) {
    }

    public function execute(Observer $event): void
    {
        $config = $event->getData('config');
        $moduleName = implode('_', array_slice(explode('\\', __CLASS__), 0, 2));

        $path = $this->componentRegistrar->getPath(ComponentRegistrar::MODULE, $moduleName);
        if (!$path || strncmp($path, BP, strlen(BP)) !== 0) {
            return;
        }

        $extensions = $config->hasData('extensions') ? $config->getData('extensions') : [];
        $extensions[] = ['src' => substr($path, strlen(BP) + 1)];
        $config->setData('extensions', $extensions);
    }
}
