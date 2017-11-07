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
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block;

use Magento\Framework\View\Design\Theme\ThemeProviderInterface;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Helper\Data as HelperData;

/**
 * Class Frontend
 *
 * @package Mageplaza\Blog\Block
 */
class Design extends Template
{
    /**
     * @type \Mageplaza\Blog\Helper\Data
     */
    public $helperData;

    /**
     * @var \Magento\Framework\View\Design\Theme\ThemeProviderInterface
     */
    protected $_themeProvider;

    /**
     * Design constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Mageplaza\Blog\Helper\Data $helperData
     * @param \Magento\Framework\View\Design\Theme\ThemeProviderInterface $_themeProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        HelperData $helperData,
        ThemeProviderInterface $_themeProvider,
        array $data = []
    )
    {
        $this->helperData     = $helperData;
        $this->_themeProvider = $_themeProvider;

        parent::__construct($context, $data);
    }

    /**
     * @return \Mageplaza\Blog\Helper\Data
     */
    public function getHelper()
    {
        return $this->helperData;
    }

    /**
     * @return mixed
     */
    public function isSidebarRight()
    {
        return $this->helperData->getBlogConfig('sidebar/sidebar_left_right');
    }

    /**
     * Get Current Theme Name Function
     * @return string
     */
    public function getCurrentTheme()
    {
        $themeId = $this->helperData->getConfigValue(DesignInterface::XML_PATH_THEME_ID);

        /** @var $theme \Magento\Framework\View\Design\ThemeInterface */
        $theme = $this->_themeProvider->getThemeById($themeId);

        return $theme->getCode();
    }
}
