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
 *                     @category  Mageplaza
 *                     @package   Mageplaza_Blog
 *                     @copyright Copyright (c) 2016
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Block\Adminhtml\Helper;

class Category extends \Magento\Framework\Data\Form\Element\Multiselect
{
    /**
     * Collection factory
     *
     * @var \Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Backend helper
     *
     * @var \Magento\Backend\Helper\Data
     */
    protected $backendData;

    /**
     * Layout instance
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * Json encoder instance
     *
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * Authorization
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

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
     * Get Category collection
     *
     * @return \Mageplaza\Blog\Model\ResourceModel\Category\Collection
     */
    protected function getCategoriesCollection()
    {
        return $this->collectionFactory->create();
    }

    /**
     * Attach Category suggest widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $htmlId = $this->getHtmlId();
        $suggestPlaceholder = __('start typing to search Category');
        $selectorOptions = $this->jsonEncoder->encode($this->getSelectorOptions());
        $newCategoryCaption = __('New Category');
        /** @var \Magento\Backend\Block\Widget\Button $button */
        $button = $this->layout->createBlock('Magento\Backend\Block\Widget\Button')
            ->setData([
                'id' => 'add_category_button',
                'label' => $newCategoryCaption,
                'title' => $newCategoryCaption,
                'onclick' => 'jQuery("#new-category").trigger("openModal")',
                'disabled' => $this->getDisabled()
            ]);
        //TODO: move this somewhere else when magento team decides to move it.
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
    }

    /**
     * Get selector options
     *
     * @return array
     */
    protected function getSelectorOptions()
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
