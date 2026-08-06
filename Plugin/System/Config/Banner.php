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
use Magento\Framework\Escaper;
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
     * @var Escaper
     */
    protected $escaper;

    /**
     * Banner constructor.
     *
     * @param Manager $moduleManager
     * @param Escaper $escaper
     */
    public function __construct(
        Manager $moduleManager,
        Escaper $escaper
    ) {
        $this->_moduleManager = $moduleManager;
        $this->escaper = $escaper;
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
        $jsonConfig = json_encode([
            'Mageplaza_Blog/js/banner-link' => [
                'image' => $bannerImg,
                'link' => 'https://www.mageplaza.com/magento-2-better-blog/'
                    . '?utm_source=dashboard&utm_medium=admin&utm_campaign=blogpro'
            ]
        ]);
        $html = '<div data-mage-init=\'' . $this->escaper->escapeHtmlAttr($jsonConfig) . '\' style="display:none;">'
            . '</div>';

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
