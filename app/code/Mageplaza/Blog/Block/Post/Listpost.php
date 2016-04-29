<?php
/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 * @category  Mageplaza
 * @package   Mageplaza_Blog
 * @copyright Copyright (c) 2016
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Block\Post;

use Magento\Framework\View\Element\Template;

use Magento\Framework\View\Element\Template\Context;
use Mageplaza\Blog\Helper\Data as HelperData;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class Listpost extends Template
{
	protected $helperData;
	protected $objectManager;
	protected $storeManager;

	public function __construct(
		Context $context,
		HelperData $helperData,
		ObjectManagerInterface $objectManager,
		StoreManagerInterface $storeManager,
		array $data = []
	)
	{
		$this->helperData    = $helperData;
		$this->objectManager = $objectManager;
		$this->storeManager  = $storeManager;
		parent::__construct($context, $data);
	}

	public function getPostList(){
		return $this->helperData->getPostList();
	}
	public function getUrlByPost($post){
		return $this->helperData->getUrlByPost($post);
	}
	public function getImageUrl($image){
		return $this->helperData->getImageUrl($image);
	}
}
