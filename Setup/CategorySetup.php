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

class CategorySetup
{
    /**
     * Setup instance
     *
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    protected $setup;

    /**
     * Category Factory
     *
     * @var \Mageplaza\Blog\Model\CategoryFactory
     */
    protected $categoryFactory;

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
     * Creates Category model
     *
     * @param array $data
     * @return \Mageplaza\Blog\Model\Category
     */
    public function createCategory($data = [])
    {
        return $this->categoryFactory->create($data);
    }
}
