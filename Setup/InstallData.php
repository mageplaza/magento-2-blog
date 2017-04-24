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

class InstallData implements \Magento\Framework\Setup\InstallDataInterface
{
    /**
     * Blog Category setup factory
     *
     * @var \Mageplaza\Blog\Setup\CategorySetupFactory
     */
    public $categorySetupFactory;

    /**
     * constructor
     *
     * @param \Mageplaza\Blog\Setup\CategorySetupFactory $categorySetupFactory
     */
    public function __construct(
		\Magento\Framework\App\State $appState,
        \Mageplaza\Blog\Setup\CategorySetupFactory $categorySetupFactory
    ) {
		$appState->setAreaCode('frontend');
        $this->categorySetupFactory = $categorySetupFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function install(\Magento\Framework\Setup\ModuleDataSetupInterface $setup,
							\Magento\Framework\Setup\ModuleContextInterface $context)
    {
    	$contextInstall = $context;
    	$contextInstall->getVersion();
        /** @var \Mageplaza\Blog\Setup\CategorySetup $categorySetup */
        $categorySetup = $this->categorySetupFactory->create(['setup' => $setup]);
        // Create Root Blog Category Node
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
