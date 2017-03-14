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
namespace Mageplaza\Blog\Setup;

class CategorySetup
{
    /**
     * Setup instance
     *
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    public $setup;

    /**
     * Blog Category Factory
     *
     * @var \Mageplaza\Blog\Model\CategoryFactory
     */
    public $categoryFactory;

    /**
     * constructor
     *
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Mageplaza\Blog\Model\CategoryFactory $categoryFactory
    ) {
    
        $this->setup           = $setup;
        $this->categoryFactory = $categoryFactory;
    }

    /**
     * Creates Blog Category model
     *
     * @param array $data
     * @return \Mageplaza\Blog\Model\Category
     */
    public function createCategory($data = [])
    {
        return $this->categoryFactory->create($data);
    }
}