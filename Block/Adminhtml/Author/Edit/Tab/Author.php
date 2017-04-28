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
namespace Mageplaza\Blog\Block\Adminhtml\Author\Edit\Tab;

class Author extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	public $systemStore;

	public function __construct(
		\Magento\Store\Model\System\Store $systemStore,
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Data\FormFactory $formFactory,
		array $data = []
	) {
		$this->systemStore = $systemStore;
		parent::__construct($context, $registry, $formFactory, $data);
	}

	/**
	 * Prepare form
	 *
	 * @return $this
	 */
	protected function _prepareForm()
	{
		$author = $this->_coreRegistry->registry('mageplaza_blog_author');
		$form = $this->_formFactory->create();
		$form->setHtmlIdPrefix('author_');
		$form->setFieldNameSuffix('author');
		$fieldset = $form->addFieldset(
			'base_fieldset',
			[
				'legend' => __('Author Info'),
				'class'  => 'fieldset-wide'
			]
		);
		$fieldset->addType('image', 'Mageplaza\Blog\Block\Adminhtml\Author\Helper\Image');
		if ($author->getId()){
			$fieldset->addField(
				'user_id',
				'hidden',
				['name' => 'user_id']
			);
		}

		$fieldset->addField(
			'name',
			'text',
			[
				'name'  => 'name',
				'label' => __('Display Name'),
				'title' => __('Display Name'),
				'note' => __('This name will displayed into frontend'),
			]
		);
		
		$fieldset->addField(
			'short_description',
			'textarea',
			[
				'name'  => 'short_description',
				'label' => __('Short Description'),
				'title' => __('Short Description'),
				'note' => __('Short Description'),
			]
		);

		$fieldset->addField(
			'image',
			'image',
			[
				'name'  => 'image',
				'label' => __('Image'),
				'title' => __('Image'),
				'note' => __('Featured image'),
			]
		);
		$fieldset->addField(
			'facebook_link',
			'text',
			[
				'name'  => 'facebook_link',
				'label' => __('Facebook'),
				'title' => __('Facebook'),
				'note' => __('Facebook Link'),
			]
		);
		$fieldset->addField(
			'twitter_link',
			'text',
			[
				'name'  => 'twitter_link',
				'label' => __('Twitter'),
				'title' => __('Twitter'),
				'note' => __('Twitter Link'),
			]
		);
		$authorData = $this->_session->getData('mageplaza_blog_author_data', true);
		if ($authorData) {
			$author->addData($authorData);
		} else {
			if (!$author->getId()) {
				$author->addData($author->getDefaultValues());
			}
		}
		$form->addValues($author->getData());
		$this->setForm($form);
		return parent::_prepareForm();
	}

	/**
	 * Prepare label for tab
	 *
	 * @return string
	 */
	public function getTabLabel()
	{
		return __('Author Info');
	}

	/**
	 * Prepare title for tab
	 *
	 * @return string
	 */
	public function getTabTitle()
	{
		return $this->getTabLabel();
	}

	/**
	 * Can show tab in tabs
	 *
	 * @return boolean
	 */
	public function canShowTab()
	{
		return true;
	}

	/**
	 * Tab is hidden
	 *
	 * @return boolean
	 */
	public function isHidden()
	{
		return false;
	}
}
