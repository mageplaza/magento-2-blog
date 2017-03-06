<?php

namespace Mageplaza\Blog\Block\Html;

use \Magento\Framework\View\Element\Template\Context;
use \Mageplaza\Blog\Helper\Data;

class Footer extends \Magento\Framework\View\Element\Html\Link
{
    public $helper;

    public function __construct(
        Context $context,
        Data $helper,
        array $data = []
    ) {
    
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    public function getHref()
    {
        return $this->helper->getBlogUrl('');
    }
}
