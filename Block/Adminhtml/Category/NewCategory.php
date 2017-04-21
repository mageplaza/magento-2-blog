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
namespace Mageplaza\Blog\Block\Adminhtml\Category;

/**
 * @method \bool getUseContainer()
 * @method NewCategory setUseContainer(\bool $use)
 */
class NewCategory extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * JSON encoder
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
	public $jsonEncoder;

    /**
     * Blog Category collection factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory
     */
	public $categoryCollectionFactory;
	public $systemStore;
	public $booleanOptions;

	/**
	 * constructor
	 *
	 * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
	 * @param \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Store\Model\System\Store $systemStore
	 * @param \Magento\Config\Model\Config\Source\Yesno $booleanOptions
	 * @param \Magento\Framework\Data\FormFactory $formFactory
	 * @param array $data
	 */
    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
		\Magento\Store\Model\System\Store $systemStore,
		\Magento\Config\Model\Config\Source\Yesno $booleanOptions,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        $this->jsonEncoder               = $jsonEncoder;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
		$this->systemStore = $systemStore;
		$this->booleanOptions    = $booleanOptions;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->setUseContainer(true);
    }

    /**
     * Form preparation
     *
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */

        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'new_category_form',
                    'class' => 'admin__scope-old'
                ]
            ]
        );
        $form->setUseContainer($this->getUseContainer());

        $form->addField('new_category_messages', 'note', []);

        $fieldset = $form->addFieldset('new_category_form_fieldset', []);

        $fieldset->addField(
            'new_category_name',
            'text',
            [
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true,
                'name' => 'new_category_name'
            ]
        );
//		$fieldset->addField(
//			'new_store_ids',
//			'multiselect',
//			[
//				'name'  => 'new_store_ids',
//				'label' => __('Store Views'),
//				'title' => __('Store Views'),
//				'note' => __('Select Store Views'),
//				'values' => $this->systemStore->getStoreValuesForForm(false, true),
//			]
//		);
//		$fieldset->addField(
//			'new_enabled',
//			'select',
//			[
//				'name'  => 'new_enabled',
//				'label' => __('Enabled'),
//				'title' => __('Enabled'),
//				'values' => $this->booleanOptions->toOptionArray(),
//			]
//		);
        // add all required fields here
        $fieldset->addField(
            'new_category_parent',
            'select',
            [
                'label' => __('Parent Category'),
                'title' => __('Parent Category'),
                'required' => false,
                'options' => $this->getParentCategoryOptions(),
                'class' => 'validate-parent-category',
                'name' => 'new_category_parent',
                // @codingStandardsIgnoreStart
                'note' => __(
                        'You can reassign the Category at any time in ' .
                        '<a href="%1" target="_blank">Manage Categories</a>.',
                    $this->getUrl('mageplaza_blog/category')
                )
                // @codingStandardsIgnoreEnd
            ]
        );
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Get parent Blog Category options
     *
     * @return array
     */
    public function getParentCategoryOptions()
    {
        $items = $this->categoryCollectionFactory->create()
            ->addOrder('Category_id', 'ASC')
            ->setPageSize(3)->load()->getItems();

        $result = [];
        if (count($items) === 2) {
            $item = array_pop($items);
            $result = [$item->getCategoryId() => $item->getName()];
        }

        return $result;
    }

    /**
     * Blog Category save action URL
     *
     * @return string
     */
    public function getSaveCategoryUrl()
    {
        return $this->getUrl('mageplaza_blog/category/save');
    }

    /**
     * Attach new Blog Category dialog widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $widgetOptions = $this->jsonEncoder->encode(
            [
                'suggestOptions' => [
                    'source' => $this->getUrl('mageplaza_blog/category/suggestCategories'),
                    'valueField' => '#new_category_parent',
                    'className' => 'category-select',
                    'multiselect' => true,
                    'showAll' => true,
                ],
                'saveCategoryUrl' => $this->getUrl('mageplaza_blog/category/save'),
            ]
        );
        //JavaScript logic should be moved to separate file or reviewed
        return <<<HTML
<script>
require(["jquery","mage/mage"],function($) {  // waiting for dependencies at first
    $(function(){ // waiting for page to load to have '#category_ids-template' available
        $('#new-category').mage('newCategoryDialog', $widgetOptions);
    });
});
</script>
HTML;
    }
}
