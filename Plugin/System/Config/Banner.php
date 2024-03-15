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

namespace Mageplaza\Blog\Plugin\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Module\Manager;
use Mageplaza\Core\Block\Adminhtml\System\Config\Docs;

/**
 * Class Banner
 * @package Mageplaza\Blog\Plugin\System\Config
 */
class Banner
{
    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * Banner constructor.
     *
     * @param Manager $moduleManager
     */
    public function __construct(
        Manager $moduleManager
    ) {
        $this->_moduleManager = $moduleManager;
    }

    /**
     * @param Docs $subject
     * @param $result
     * @param AbstractElement $element
     *
     * @return mixed
     */
    public function afterRender(Docs $subject, $result, AbstractElement $element)
    {
        if ($this->isHideBanner($element)) {
            return $result;
        }
        $bannerImg = $subject->getViewFileUrl('Mageplaza_Blog::media/banner/banner.png');
        $html      = <<<HTML
        <script>
            require([ 'jquery'], function ($) {
                var session = $(".accordion" );
                $("<a target='_blank' href='https://www.mageplaza.com/magento-2-better-blog/?utm_source=dashboard&utm_medium=admin&utm_campaign=blogpro'>" +
                 "<img src='{$bannerImg}'></a>").insertBefore(session);
            })
        </script>
        HTML;

        $result = $html . $result;

        return $result;
    }

    /**
     * @param $element
     * @return bool
     */
    protected function isHideBanner($element)
    {
        if ($element->getOriginalData()['module_name'] !== 'Mageplaza_Blog') {
            return true;
        }

        if ($this->_moduleManager->isOutputEnabled('Mageplaza_BlogPro')) {
            return true;
        }

        return false;
    }
}
