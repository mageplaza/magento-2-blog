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
declare(strict_types=1);

namespace Mageplaza\Blog\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Model\AuthorFactory;

/**
 * Class InsertData
 *
 * @package Mageplaza\Blog\Setup\Patch\Data
 */
class InsertData implements
    DataPatchInterface,
    PatchRevertableInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * Date model
     *
     * @var DateTime
     */
    protected $date;

    /**
     * @var AuthorFactory
     */
    protected $authorFactory;

    /**
     * InsertData constructor.
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param AuthorFactory $authorFactory
     * @param DateTime $date
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        AuthorFactory $authorFactory,
        DateTime $date
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->authorFactory   = $authorFactory;
        $this->date            = $date;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $defaultData = [
            'name'       => 'Admin',
            'type'       => 0,
            'status'     => 1,
            'created_at' => $this->date->date()
        ];

        if (!$this->authorFactory->create()->getCollection()->getSize()) {
            $this->moduleDataSetup->getConnection()->insertOnDuplicate(
                $this->moduleDataSetup->getTable('mageplaza_blog_author'),
                $defaultData
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function revert()
    {
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}
