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
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Blog\Block\Adminhtml\System\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Head extends \Magento\Config\Block\System\Config\Form\Field
{


	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		array $data = []
	) {
		parent::__construct($context, $data);
	}

	/**
	 * Set template
	 *
	 * @return void
	 */
	protected function _construct()
	{
		parent::_construct();
		$this->setTemplate('Mageplaza_Blog::system/config/head.phtml');
	}

	/**
	 * Render text
	 *
	 * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
	 * @return string
	 */
	public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
	{
//		$html='<li class="notice-msg">
//
//                                        * <a href="https://mageplaza.freshdesk.com/support/solutions/folders/6000213306" target="_blank">User Guide</a> <br>
//                                        * <a href="https://mageplaza.freshdesk.com/support/discussions/topics/new?forum_id=6000240988" target="_blank">Report a problem</a> <br>
//                                        * Your default blog URL <strong>domain.com/blog/</strong> <br>
//                                        * Atom Feed <strong>domain.com/blog/post/rss/</strong> <br>
//                                        * Sitemap <strong>domain.com/sitemap/blog.xml</strong> <br>
//
//                            </li>';
//		return $html;
		
		return parent::render($element);
	}

	/**
	 * Return element html
	 *
	 * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
	 * @return string
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
	{
		return $this->_toHtml();
	}
}
