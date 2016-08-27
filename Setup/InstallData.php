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
namespace Mageplaza\Blog\Setup;

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * Category setup factory
     *
     * @var \Mageplaza\Blog\Setup\CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * constructor
     *
     * @param \Mageplaza\Blog\Setup\CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
        \Mageplaza\Blog\Setup\CategorySetupFactory $categorySetupFactory
    ) {
    
        $this->categorySetupFactory = $categorySetupFactory;
    }


    /**
     * {@inheritdoc}
     */
    public function install(\Magento\Framework\Setup\ModuleDataSetupInterface $setup, \Magento\Framework\Setup\ModuleContextInterface $context)
    {
        /** @var \Mageplaza\Blog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        // Create Root Category Node
        $category = $categorySetup->createCategory();
        $category
            ->setPath('1')
            ->setLevel(0)
            ->setPosition(0)
            ->setChildrenCount(0)
            ->setName('ROOT')
            ->setInitialSetupFlag(true)
            ->save();
    }
}
