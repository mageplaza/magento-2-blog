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

namespace Mageplaza\Blog\Setup;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Model\CommentFactory;
use Mageplaza\Blog\Model\Config\Source\SideBarLR;
use Mageplaza\Blog\Helper\Data as BlogHelper;


class UpgradeData implements UpgradeDataInterface
{
    const SIDEBAR_LR_MAPPING = [
        '0' => SideBarLR::LEFT,
        '1' => SideBarLR::RIGHT
    ];

    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * @var CommentFactory
     */
    public $comment;

    /**
     * @var $configInterface;
     */
    private $configInterface;

    /**
     * UpgradeData constructor.
     * @param DateTime $date
     * @param CommentFactory $commentFactory
     * @param ConfigInterface $configInterface
     */
    public function __construct(
        DateTime $date,
        CommentFactory $commentFactory,
        ConfigInterface $configInterface
    )
    {
        $this->comment = $commentFactory;
        $this->date    = $date;
        $this->configInterface = $configInterface;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '2.4.4', '<')) {
            $commentIds = $this->comment->create()->getCollection()->getAllIds();
            $commentIds = implode(',', $commentIds);
            if ($commentIds) {
                /** Add create at old comment */
                $sampleTemplates = [
                    'created_at' => $this->date->date(),
                    'status'     => 3
                ];
                $setup->getConnection()->update($setup->getTable('mageplaza_blog_comment'), $sampleTemplates, 'comment_id IN (' . $commentIds . ')');
            }
        }

        if (version_compare($context->getVersion(), '2.5.0', '<')) {
            $this->upgradeSidebarConstants($setup, $context);
        }

        $installer->endSetup();
    }

    /**
     * Method maps old 'sidebar_left_right' values to 3.0.3 equivalents.
     * @param ModuleDataSetupInterface $setup
     */
    private function upgradeSidebarConstants(ModuleDataSetupInterface $setup)
    {
        $configPath = BlogHelper::CONFIG_MODULE_PATH . '/sidebar/sidebar_left_right';
        $select = $setup->getConnection()->select()
            ->from(
                $setup->getTable('core_config_data'),
                [
                    'scope',
                    'scope_id',
                    'path',
                    'value'
                ]
            )
            ->where('path=?', $configPath);

        $results = $setup->getConnection()->query($select)->fetchAll();

        foreach ($results as $result) {
            if (!array_key_exists($result['value'], self::SIDEBAR_LR_MAPPING)) {
                continue;
            }

            $upgradedValue = self::SIDEBAR_LR_MAPPING[$result['value']];
            $this->configInterface->saveConfig($configPath, $upgradedValue, $result['scope'], $result['scope_id']);
        }
    }
}
