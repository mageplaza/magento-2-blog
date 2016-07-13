<?php
namespace Mageplaza\Blog\Block\Html;
class Init extends \Magento\Backend\Block\AbstractBlock
{
	/**
	 * @override
	 * @see \Magento\Backend\Block\AbstractBlock::_construct()
	 * @return void
	 */
	protected function _construct()
	{
		/** http://devdocs.magento.com/guides/v2.0/architecture/view/page-assets.html#m2devgde-page-assets-api */
		/** @var \Magento\Framework\App\ObjectManager $om */
		$om = \Magento\Framework\App\ObjectManager::getInstance();
		/** @var \Magento\Framework\View\Page\Config $page */
		$page = $om->get('Magento\Framework\View\Page\Config');
		$page->addPageAsset('Mageplaza_Blog::css/index/mp.css');
		$page->addPageAsset('Mageplaza_Blog::js/bootstrap.min.js');
	}
}