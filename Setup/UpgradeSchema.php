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

			$tagTable = $installer->getTable('mageplaza_blog_tag');
			if ($installer->getConnection()->isTableExists($tagTable) == true) {
				$columns = [
					'meta_title' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'length' => 255,
						'comment' => 'Post Meta Title',
					],
					'meta_description' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'length' => '64k',
						'comment' => 'Post Meta Description',
					],
					'meta_keywords' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'length' => '64k',
						'comment' => 'Post Meta Keywords',
					],
					'meta_robots' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
						'length' => '64k',
						'comment' => 'Post Meta Robots',
					]
				];

				$connection = $installer->getConnection();
				foreach ($columns as $name => $definition) {
					$connection->addColumn($tagTable,$name,$definition);
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

			if (! $installer->tableExists('mageplaza_blog_comment')) {
				$table = $installer->getConnection()
					->newTable($installer->getTable('mageplaza_blog_comment'));
				$table->addColumn(
					'comment_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'unsigned' => true,
						'nullable' => false,
						'primary'  => true,
					],
					'Comment ID'
				)->addColumn(
					'post_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'unsigned' => true,
						'nullable' => false,
					],
					'Post ID'
				)->addColumn(
					'entity_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'unsigned' => true,
						'nullable' => false,
					],
					'User Comment ID'
				)->addColumn(
					'has_reply',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					2,
					[
						'unsigned' 	=> true,
						'nullable' 	=> false,
						'default'	=> 0
					],
					'Comment has reply'
				)->addColumn(
					'is_reply',
					\Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
					2,
					[
						'unsigned' 	=> true,
						'nullable' 	=> false,
						'default'	=> 0
					],
					'Is reply comment'
				)->addColumn(
					'reply_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'unsigned' => true,
						'nullable' => true,
						'default'  => 0
					],
					'Reply ID'
				)->addColumn(
					'content',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Comment content'
				)->addColumn(
					'created_at',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					null,
					[],
					'Comment Created At'
				)->addIndex(
					$installer->getIdxName('mageplaza_blog_comment', ['comment_id']),
					['comment_id']
				)->addIndex(
					$installer->getIdxName('mageplaza_blog_comment', ['entity_id']),
					['entity_id']
				)->addForeignKey(
					$installer->getFkName(
						'mageplaza_blog_comment',
						'entity_id',
						'customer_entity',
						'entity_id'
					),
					'entity_id',
					$installer->getTable('customer_entity'),
					'entity_id',
					\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
					\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
				)->addForeignKey(
					$installer->getFkName(
						'mageplaza_blog_comment',
						'post_id',
						'mageplaza_blog_post',
						'post_id'
					),
					'post_id',
					$installer->getTable('mageplaza_blog_post'),
					'post_id',
					\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
					\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
				);
				$installer->getConnection()->createTable($table);
			}
			if (! $installer->tableExists('mageplaza_blog_comment_like')) {
				$table = $installer->getConnection()
					->newTable($installer->getTable('mageplaza_blog_comment_like'));
				$table->addColumn(
					'like_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'identity' => true,
						'unsigned' => true,
						'nullable' => false,
						'primary'  => true,
					],
					'Like ID'
				)->addColumn(
					'comment_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'unsigned' => true,
						'nullable' => false,
					],
					'Comment ID'
				)->addColumn(
					'entity_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'unsigned' => true,
						'nullable' => false,
					],
					'User Like ID'
				)->addIndex(
					$installer->getIdxName('mageplaza_blog_comment_like', ['like_id']),
					['like_id']
				)->addForeignKey(
					$installer->getFkName(
						'mageplaza_blog_comment_like',
						'comment_id',
						'mageplaza_blog_comment',
						'comment_id'
					),
					'comment_id',
					$installer->getTable('mageplaza_blog_comment'),
					'comment_id',
					\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
					\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
				)->addForeignKey(
					$installer->getFkName(
						'mageplaza_blog_comment_like',
						'entity_id',
						'customer_entity',
						'entity_id'
					),
					'entity_id',
					$installer->getTable('customer_entity'),
					'entity_id',
					\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
					\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
				);
				$installer->getConnection()->createTable($table);
			}

		}
		if (version_compare($context->getVersion(), '1.1.3', '<')) {
			if ($installer->getConnection()->isTableExists('mageplaza_blog_author') == true) {
				$columns = [
					'image' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'length' => 255,
						'comment' => 'Author Image',
						'unsigned' => true,
					],
					'short_description' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'length' => '64k',
						'comment' => 'Author Short Description',
						'unsigned' => true,
					],
					'facebook_link' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'length' => 255,
						'comment' => 'Facebook Link',
						'unsigned' => true,
					],
					'twitter_link' => [
						'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
						'length' => 255,
						'comment' => 'Twitter Link',
						'unsigned' => true,
					],
				];

				$connection = $installer->getConnection();
				foreach ($columns as $name => $definition) {
					$connection->addColumn('mageplaza_blog_author',$name,$definition);
				}
			}
		}
    }
}
