<?php

namespace Mageplaza\Blog\Plugin;

use Magento\Framework\Data\Tree\NodeFactory;

class Topmenu
{
    protected $helper;

    public function __construct(
        \Mageplaza\Blog\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    public function afterGetHtml(\Magento\Theme\Block\Html\Topmenu $topmenu, $html)
    {


        $html .= "<li class=\"level0 level-top ui-menu-item\">";
        $html .= "<a href=\"" . $this->helper->getBlogUrl('')
            . "\" class=\"level-top ui-corner-all\" aria-haspopup=\"true\" tabindex=\"-1\" role=\"menuitem\">
			<span class=\"ui-menu-icon ui-icon ui-icon-carat-1-e\"></span><span>"
            . $this->helper->getBlogConfig('general/name') . "</span></a>";
        $html .= "</li>";
        return $html;
    }
}
