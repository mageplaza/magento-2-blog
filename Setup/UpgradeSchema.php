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
		$contextInstall = $context;
		$contextInstall->getVersion();
        $installer = $setup;
        $installer->startSetup();
		if (version_compare($context->getVersion(), '1.1.1', '<')) {

			$table =$installer->getTable('mageplaza_blog_post');
			if ($installer->getConnection()->isTableExists($table) == true) {
				$columns = [
					'author_id' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
						'comment' => 'Author ID',
						'unsigned' => true,
					],
					'modifier_id' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
						'comment' => 'Modifier ID',
						'unsigned' => true,
					],
				];

				$connection = $installer->getConnection();
				foreach ($columns as $name => $definition) {
					$connection->addColumn($table,$name,$definition);
				}
			}

			if (! $installer->tableExists('mageplaza_blog_post_traffic')) {
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
			if (! $installer->tableExists('mageplaza_blog_author')) {
				$table = $installer->getConnection()
					->newTable($installer->getTable('mageplaza_blog_author'));
				$table->addColumn(
					'user_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'unsigned' => true,
						'nullable' => false,
						'primary'  => true,
					],
					'User ID'
				)
					->addColumn(
						'name',
						\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						255,
						[],
						'Display Name'
					)
					->addColumn(
						'url_key',
						\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						255,
						[],
						'Author URL Key'
					)
					->addColumn(
						'created_at',
						\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
						null,
						[
							'default'=>	\Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
						],
						'Author Created At'
					)
					->addColumn(
						'updated_at',
						\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
						null,
						[
							'default'=>	\Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT
						],
						'Author Updated At'
					)
					->addForeignKey(
						$installer->getFkName(
							'mageplaza_blog_author',
							'user_id',
							'admin_user',
							'user_id'
						),
						'user_id',
						$installer->getTable('admin_user'),
						'user_id',
						\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
						\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
					)
					->addIndex(
						$installer->getIdxName(
							'mageplaza_blog_author',
							[
								'user_id'
							],
							\Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
						),
						[
							'user_id'
						],
						[
							'type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
						]
					)
					->setComment('Author Table');
				$installer->getConnection()->createTable($table);
			}

			$installer->endSetup();
		}
		if (version_compare($context->getVersion(), '1.1.2', '<')) {
			if ($installer->getConnection()->isTableExists('mageplaza_blog_post') == true) {
				$connection = $installer->getConnection();
				$connection->modifyColumn('mageplaza_blog_post','meta_robots',['type' =>\Magento\Framework\DB\Ddl\Table::TYPE_TEXT]);
			}
			if ($installer->getConnection()->isTableExists('mageplaza_blog_tag') == true) {
				$connection = $installer->getConnection();
				$connection->modifyColumn('mageplaza_blog_tag','meta_robots',['type' =>\Magento\Framework\DB\Ddl\Table::TYPE_TEXT]);
			}
			if ($installer->getConnection()->isTableExists('mageplaza_blog_category') == true) {
				$connection = $installer->getConnection();
				$connection->modifyColumn('mageplaza_blog_category','meta_robots',['type' =>\Magento\Framework\DB\Ddl\Table::TYPE_TEXT]);
			}
			if ($installer->getConnection()->isTableExists('mageplaza_blog_category') == true) {
				$connection = $installer->getConnection();
				$connection->modifyColumn('mageplaza_blog_topic','meta_robots',['type' =>\Magento\Framework\DB\Ddl\Table::TYPE_TEXT]);
			}
		}
    }
}
