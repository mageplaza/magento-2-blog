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
     *
     * @return mixed
     */
    public function afterRender(Docs $subject, $result, AbstractElement $element)
    {
        if ($element->getOriginalData()['module_name'] !== 'Mageplaza_Blog'
            && $this->checkValidate()) {
            return $result;
        }
        $bannerImg = $subject->getViewFileUrl('Mageplaza_Blog::media/banner/banner.png');
        $html      = <<<HTML
        <img src="{$bannerImg}">
        HTML;

        $result = $html . $result;

        return $result;
    }

    /**
     * @return bool
     */
    protected function checkValidate()
    {
        return $this->_moduleManager->isOutputEnabled('Mageplaza_BlogPro');
    }
}
