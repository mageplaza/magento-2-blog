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
namespace Mageplaza\Blog\Block\Adminhtml\Helper;

class Category extends \Magento\Framework\Data\Form\Element\Multiselect
{
    /**
     * Collection factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory
     */
	public $collectionFactory;

    /**
     * Backend helper
     *
     * @var \Magento\Backend\Helper\Data
     */
	public $backendData;

    /**
     * Layout instance
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
	public $layout;

    /**
     * Json encoder instance
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
	public $jsonEncoder;

    /**
     * Authorization
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
	public $authorization;

    /**
     * constructor
     *
     * @param \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $collectionFactory
     * @param \Magento\Backend\Helper\Data $backendData
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\AuthorizationInterface $authorization
     * @param \Magento\Framework\Data\Form\Element\Factory $factoryElement
     * @param \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection
     * @param \Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory $collectionFactory,
        \Magento\Backend\Helper\Data $backendData,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\AuthorizationInterface $authorization,
        \Magento\Framework\Data\Form\Element\Factory $factoryElement,
        \Magento\Framework\Data\Form\Element\CollectionFactory $factoryCollection,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
    
        $this->collectionFactory = $collectionFactory;
        $this->backendData       = $backendData;
        $this->layout            = $layout;
        $this->jsonEncoder       = $jsonEncoder;
        $this->authorization     = $authorization;
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * Get no display
     *
     * @return bool
     */
    public function getNoDisplay()
    {
        $isNotAllowed = !$this->authorization->isAllowed('Mageplaza_Blog::category');
        return $this->getData('no_display') || $isNotAllowed;
    }

    /**
     * Get values for select
     *
     * @return array
     */
    public function getValues()
    {
        $collection = $this->getCategoriesCollection();
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = explode(',', $values);
        }
        $collection->addIdFilter($values);
        $options = [];
        foreach ($collection as $category) {
            /** @var \Mageplaza\Blog\Model\Category $category */
            $options[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }
        return $options;
    }

    /**
     * Get Blog Category collection
     *
     * @return \Mageplaza\Blog\Model\ResourceModel\Category\Collection
     */
	public function getCategoriesCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * Attach Blog Category suggest widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $htmlId = $this->getHtmlId();
        $suggestPlaceholder = __('start typing to search Blog Category');
        $selectorOptions = $this->jsonEncoder->encode($this->getSelectorOptions());
        $newCategoryCaption = __('New Blog Category');
        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button = $this->layout->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData([
                'id' => 'add_category_button',
                'label' => $newCategoryCaption,
                'title' => $newCategoryCaption,
                'onclick' => 'jQuery("#new-category").trigger("openModal")',
                'disabled' => $this->getDisabled()
            ]);
        // move this somewhere else when magento team decides to move it.
        $return = <<<HTML
        <input id="{$htmlId}-suggest" placeholder="$suggestPlaceholder" />
        <script type="text/javascript">
            require(["jquery","mage/mage"],function($) {  // waiting for dependencies at first
                $(function(){ // waiting for page to load to have '#category_ids-template' available
                    $('#{$htmlId}-suggest').mage('treeSuggest', {$selectorOptions});
                });
            });
        </script>
HTML;
        return $return . $button->toHtml();
//		return $return;
    }

    /**
     * Get selector options
     *
     * @return array
     */
	public function getSelectorOptions()
    {
        return [
            'source' => $this->backendData->getUrl('mageplaza_blog/category/suggestCategories'),
            'valueField' => '#' . $this->getHtmlId(),
            'className' => 'category-select',
            'multiselect' => true,
            'showAll' => true
        ];
    }
}
