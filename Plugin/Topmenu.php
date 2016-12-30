<?php

namespace Mageplaza\Blog\Plugin;

use Magento\Framework\Data\Tree\NodeFactory;

class Topmenu
{
	/**
	 * @var NodeFactory
	 */
	protected $nodeFactory;
	
	protected $helper;

	public function __construct(
		NodeFactory $nodeFactory,
		\Mageplaza\Blog\Helper\Data $helper
	) {
		$this->helper = $helper;
		$this->nodeFactory = $nodeFactory;
	}

	public function beforeGetHtml(
		\Magento\Theme\Block\Html\Topmenu $subject,
		$outermostClass = '',
		$childrenWrapClass = '',
		$limit = 0
	) {
		$node = $this->nodeFactory->create(
			[
				'data' => $this->getNodeAsArray(),
				'idField' => 'id',
				'tree' => $subject->getMenu()->getTree()
			]
		);
		$subject->getMenu()->addChild($node);
	}

	protected function getNodeAsArray()
	{
		return [
			'name' => $this->helper->getBlogConfig('general/name'),
			'id' => 'mp-blog-topmenu',
			'url' => $this->helper->getBlogUrl(''),
			'has_active' => false,
			'is_active' => false // (expression to determine if menu item is selected or not)
		];
	}
}