<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageplaza\Blog\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists('mageplaza_blog_post_traffic')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_blog_post_traffic'));
            $table->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'   => true,
                ],
                'Post ID'
            )
                ->addColumn(
                    'traffic_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'Traffic ID'
                )
                ->addColumn(
                    'numbers_view',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Numbers View'
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_blog_post_traffic', ['post_id']),
                    ['post_id']
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_blog_post_traffic', ['traffic_id']),
                    ['traffic_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_blog_post_traffic',
                        'post_id',
                        'mageplaza_blog_post',
                        'post_id'
                    ),
                    'post_id',
                    $installer->getTable('mageplaza_blog_post'),
                    'post_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName(
                        'mageplaza_blog_post_traffic',
                        [
                            'post_id',
                            'traffic_id'
                        ],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'post_id',
                        'traffic_id'
                    ],
                    [
                        'type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ]
                )
                ->setComment('Traffic Post Table');
            $installer->getConnection()->createTable($table);
        }

        $installer->endSetup();
    }
}
