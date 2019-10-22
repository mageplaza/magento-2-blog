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
 * @package     Mageplaza_Faqs
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Mageplaza\Blog\Model\AuthorFactory;
use Mageplaza\Blog\Model\ResourceModel\Author\Collection;

/**
 * Class Author
 * @package Mageplaza\Faqs\Model\Config\Source
 */
class Author implements ArrayInterface
{
    /**
     * @var AuthorFactory
     */
    public $_authorFactory;

    public function __construct(
        AuthorFactory $authorFactory
    ) {
        $this->_authorFactory = $authorFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getAuthors() as $value => $author) {
            $options[] = [
                'value' => $value,
                'label' => $author->getName()
            ];
        }

        return $options;
    }

    /**
     * @return Collection
     */
    public function getAuthors()
    {
        return $this->_authorFactory->create()->getCollection();
    }
}
