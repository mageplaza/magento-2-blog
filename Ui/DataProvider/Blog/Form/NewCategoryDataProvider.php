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
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Ui\DataProvider\Blog\Form;

use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Mageplaza\Blog\Model\ResourceModel\Category\CollectionFactory;

/**
 * DataProvider for new category form
 */
class NewCategoryDataProvider extends AbstractDataProvider
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);

        $this->collection = $collectionFactory->create();
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $this->data = array_replace_recursive(
            $this->data,
            [
                'config' => [
                    'data' => [
                        'is_active' => 1,
                        'include_in_menu' => 1,
                        'return_session_messages_only' => 1,
                        'use_config' => ['available_sort_by', 'default_sort_by']
                    ]
                ]
            ]
        );

        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function getMeta()
    {
        $this->meta = [
            'data' => [
                'children' => [
                    'parent' => [
                        'notice' => $this->getNotice(),
                    ]
                ]
            ]
        ];

        return parent::getMeta();
    }

    /**
     * Get notice message
     *
     * @return Phrase
     */
    protected function getNotice()
    {
        return __(
            'If there are no custom parent categories, please use the default parent category. You can reassign the category at any time in <a href="%1" target="_blank">Blogs &gt; Categories</a>.',
            $this->urlBuilder->getUrl('mageplaza_blog/category')
        );
    }
}
