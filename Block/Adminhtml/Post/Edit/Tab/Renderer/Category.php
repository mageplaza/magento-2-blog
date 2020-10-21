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
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer;

use Magento\Catalog\Model\Category as CategoryModel;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Multiselect;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Mageplaza\Blog\Model\ResourceModel\Category\Collection;
use Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory as BlogCategoryCollectionFactory;

/**
 * Class Category
 * @package Mageplaza\Blog\Block\Adminhtml\Post\Edit\Tab\Renderer
 */
class Category extends Multiselect
{
    /**
     * @var BlogCategoryCollectionFactory
     */
    public $collectionFactory;

    /**
     * @var AuthorizationInterface
     */
    public $authorization;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * Category constructor.
     *
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param BlogCategoryCollectionFactory $collectionFactory
     * @param AuthorizationInterface $authorization
     * @param UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        BlogCategoryCollectionFactory $collectionFactory,
        AuthorizationInterface $authorization,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->authorization = $authorization;
        $this->_urlBuilder = $urlBuilder;

        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
    }

    /**
     * @inheritdoc
     */
    public function getElementHtml()
    {
        $html = '<div class="admin__field-control admin__control-grouped">';
        $html .= '<div id="blog-category-select" class="admin__field"
                    data-bind="scope:\'blogCategory\'" data-index="index">';
        $html .= '<!-- ko foreach: elems() -->';
        $html .= '<input name="post[categories_ids]" data-bind="value: value" style="display: none"/>';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '<!-- /ko -->';
        $html .= '</div>';

        $html .= '<div class="admin__field admin__field-group-additional admin__field-small"
                  data-bind="scope:\'create_category_button\'">';
        $html .= '<div class="admin__field-control">';
        $html .= '<!-- ko template: elementTmpl --><!-- /ko -->';
        $html .= '</div></div></div>';

        $html .= '<!-- ko scope: \'create_category_modal\' -->
        <!-- ko template: getTemplate() --><!-- /ko --><!-- /ko -->';

        $html .= $this->getAfterElementHtml();

        return $html;
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
     * @return mixed
     */
    public function getCategoriesTree()
    {
        /* @var $collection \Magento\Catalog\Model\ResourceModel\Category\Collection */
        $collection = $this->collectionFactory->create();

        $categoryById = [
            CategoryModel::TREE_ROOT_ID => [
                'value' => CategoryModel::TREE_ROOT_ID,
                'optgroup' => null,
            ],
        ];

        foreach ($collection as $category) {
            foreach ([$category->getId(), $category->getParentId()] as $categoryId) {
                if (!isset($categoryById[$categoryId])) {
                    $categoryById[$categoryId] = ['value' => $categoryId];
                }
            }

            $categoryById[$category->getId()]['is_active'] = 1;
            $categoryById[$category->getId()]['label'] = $category->getName();
            $categoryById[$category->getParentId()]['optgroup'][] = &$categoryById[$category->getId()];
        }

        return $categoryById[CategoryModel::TREE_ROOT_ID]['optgroup'];
    }

    /**
     * Get values for select
     *
     * @return array
     */
    public function getValues()
    {
        $values = $this->getValue();
        if (!is_array($values)) {
            $values = explode(',', $values);
        }

        if (!count($values)) {
            return [];
        }

        /* @var $collection Collection */
        $collection = $this->collectionFactory->create()
            ->addIdFilter($values);

        $options = [];
        foreach ($collection as $category) {
            $options[] = $category->getId();
        }

        return $options;
    }

    /**
     * Attach Blog Category suggest widget initialization
     *
     * @return string
     */
    public function getAfterElementHtml()
    {
        $html = '<script type="text/x-magento-init">
            {
                "*": {
                    "Magento_Ui/js/core/app": {
                        "components": {
                            "blogCategory": {
                                "component": "uiComponent",
                                "children": {
                                    "blog_select_category": {
                                        "component": "Mageplaza_Blog/js/components/new-category",
                                        "config": {
                                            "filterOptions": true,
                                            "disableLabel": true,
                                            "chipsEnabled": true,
                                            "levelsVisibility": "1",
                                            "elementTmpl": "ui/grid/filters/elements/ui-select",
                                            "options": ' . json_encode($this->getCategoriesTree()) . ',
                                            "value": ' . json_encode($this->getValues()) . ',
                                            "listens": {
                                                "index=create_category:responseData": "setParsed",
                                                "newOption": "toggleOptionSelected"
                                            },
                                            "config": {
                                                "dataScope": "blog_select_category",
                                                "sortOrder": 10
                                            }
                                        }
                                    }
                                }
                            },
                            "create_category_button": {
                                "title": "' . __('New Category') . '",
                                "formElement": "container",
                                "additionalClasses": "admin__field-small",
                                "componentType": "container",
                                "component": "Magento_Ui/js/form/components/button",
                                "template": "ui/form/components/button/container",
                                "actions": [
                                    {
                                        "targetName": "create_category_modal",
                                        "actionName": "toggleModal"
                                    },
                                    {
                                        "targetName": "create_category_modal.create_category",
                                        "actionName": "render"
                                    },
                                    {
                                        "targetName": "create_category_modal.create_category",
                                        "actionName": "resetForm"
                                    }
                                ],
                                "additionalForGroup": true,
                                "provider": false,
                                "source": "product_details",
                                "displayArea": "insideGroup"
                            },
                            "create_category_modal": {
                                "config": {
                                    "isTemplate": false,
                                    "componentType": "container",
                                    "component": "Magento_Ui/js/modal/modal-component",
                                    "options": {
                                        "title": "' . __('New Category') . '",
                                        "type": "slide"
                                    },
                                    "imports": {
                                        "state": "!index=create_category:responseStatus"
                                    }
                                },
                                "children": {
                                    "create_category": {
                                        "label": "",
                                        "componentType": "container",
                                        "component": "Magento_Ui/js/form/components/insert-form",
                                        "dataScope": "",
                                        "update_url": "' . $this->_urlBuilder->getUrl('mui/index/render') . '",
                                        "render_url": "' .
            $this->_urlBuilder->getUrl(
                'mui/index/render_handle',
                ['handle' => 'mageplaza_blog_category_create', 'buttons' => 1]
            ) . '",
                                        "autoRender": false,
                                        "ns": "blog_new_category_form",
                                        "externalProvider": "blog_new_category_form.new_category_form_data_source",
                                        "toolbarContainer": "${ $.parentName }",
                                        "formSubmitType": "ajax"
                                    }
                                }
                            }
                        }
                    }
                }
            }
        </script>';

        return $html;
    }
}
