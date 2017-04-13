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

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    /**
     * install tables
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $installer = $setup;
        $contextInstall = $context;
        $contextInstall->getVersion();
        $installer->startSetup();
        if (! $installer->tableExists('mageplaza_blog_post')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mageplaza_blog_post')
            )
                ->addColumn(
                    'post_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'Post ID'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Post Name'
                )
                ->addColumn(
                    'short_description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Post Short Description'
                )
                ->addColumn(
                    'post_content',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Post Content'
                )
                ->addColumn(
                    'store_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Store Id'
                )
                ->addColumn(
                    'image',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Post Image'
                )
                ->addColumn(
                    'views',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Post Views'
                )
                ->addColumn(
                    'enabled',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    [],
                    'Post Enabled'
                )
                ->addColumn(
                    'url_key',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Post URL Key'
                )
                ->addColumn(
                    'in_rss',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    [],
                    'Post In RSS'
                )
                ->addColumn(
                    'allow_comment',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    [],
                    'Post Allow Comment'
                )
                ->addColumn(
                    'meta_title',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Post Meta Title'
                )
                ->addColumn(
                    'meta_description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Post Meta Description'
                )
                ->addColumn(
                    'meta_keywords',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Post Meta Keywords'
                )
                ->addColumn(
                    'meta_robots',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Post Meta Robots'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Post Created At'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Post Updated At'
                )
				->addColumn(
					'author_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'unsigned' => true,
					],
					'Author ID'
				)
				->addColumn(
					'modifier_id',
					\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
					null,
					[
						'unsigned' => true,
					],
					'Modifier ID'
				)
                ->setComment('Post Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('mageplaza_blog_post'),
                $setup->getIdxName(
                    $installer->getTable('mageplaza_blog_post'),
                    ['name', 'short_description', 'post_content', 'image', 'url_key', 'meta_title', 'meta_description',
                     'meta_keywords'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['name', 'short_description', 'post_content', 'image', 'url_key', 'meta_title', 'meta_description',
                 'meta_keywords'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        if (! $installer->tableExists('mageplaza_blog_tag')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mageplaza_blog_tag')
            )
                ->addColumn(
                    'tag_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'Tag ID'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Tag Name'
                )
                ->addColumn(
                    'description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Tag Description'
                )
                ->addColumn(
                    'store_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Store Id'
                )
                ->addColumn(
                    'enabled',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    [],
                    'Tag Enabled'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Tag Created At'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Tag Updated At'
                )->addColumn(
                    'url_key',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Tag URL Key'
                )
                ->setComment('Tag Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('mageplaza_blog_tag'),
                $setup->getIdxName(
                    $installer->getTable('mageplaza_blog_tag'),
                    ['name', 'description'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['name', 'description'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        if (! $installer->tableExists('mageplaza_blog_topic')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mageplaza_blog_topic')
            )
                ->addColumn(
                    'topic_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'Topic ID'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Topic Name'
                )
                ->addColumn(
                    'description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Topic Description'
                )
                ->addColumn(
                    'store_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Store Id'
                )
                ->addColumn(
                    'enabled',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    [],
                    'Topic Enabled'
                )
                ->addColumn(
                    'url_key',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Topic URL Key'
                )
                ->addColumn(
                    'meta_title',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Topic Meta Title'
                )
                ->addColumn(
                    'meta_description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Topic Meta Description'
                )
                ->addColumn(
                    'meta_keywords',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Topic Meta Keywords'
                )
                ->addColumn(
                    'meta_robots',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Topic Meta Robots'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Topic Created At'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Topic Updated At'
                )
                ->setComment('Topic Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('mageplaza_blog_topic'),
                $setup->getIdxName(
                    $installer->getTable('mageplaza_blog_topic'),
                    ['name', 'description', 'url_key', 'meta_title', 'meta_description', 'meta_keywords'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['name', 'description', 'url_key', 'meta_title', 'meta_description', 'meta_keywords'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        if (! $installer->tableExists('mageplaza_blog_category')) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable('mageplaza_blog_category')
            )
                ->addColumn(
                    'category_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'Category ID'
                )
                ->addColumn(
                    'name',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Category Name'
                )
                ->addColumn(
                    'description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Category Description'
                )
                ->addColumn(
                    'store_ids',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true,
                    ],
                    'Store Id'
                )
                ->addColumn(
                    'url_key',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Category URL Key'
                )
                ->addColumn(
                    'enabled',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    1,
                    [],
                    'Category Enabled'
                )
                ->addColumn(
                    'meta_title',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Category Meta Title'
                )
                ->addColumn(
                    'meta_description',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Category Meta Description'
                )
                ->addColumn(
                    'meta_keywords',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    '64k',
                    [],
                    'Category Meta Keywords'
                )
                ->addColumn(
                    'meta_robots',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Category Meta Robots'
                )
                ->addColumn(
                    'parent_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Category Parent Id'
                )
                ->addColumn(
                    'path',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    255,
                    [],
                    'Category Path'
                )
                ->addColumn(
                    'position',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Category Position'
                )
                ->addColumn(
                    'level',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Category Level'
                )
                ->addColumn(
                    'children_count',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [],
                    'Category Children Count'
                )
                ->addColumn(
                    'created_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Category Created At'
                )
                ->addColumn(
                    'updated_at',
                    \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
                    null,
                    [],
                    'Category Updated At'
                )
                ->setComment('Category Table');
            $installer->getConnection()->createTable($table);

            $installer->getConnection()->addIndex(
                $installer->getTable('mageplaza_blog_category'),
                $setup->getIdxName(
                    $installer->getTable('mageplaza_blog_category'),
                    ['name', 'description', 'url_key', 'meta_title', 'meta_description', 'meta_keywords'],
                    \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
                ),
                ['name', 'description', 'url_key', 'meta_title', 'meta_description', 'meta_keywords'],
                \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_FULLTEXT
            );
        }
        if (! $installer->tableExists('mageplaza_blog_post_tag')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_blog_post_tag'));
            $table->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Post ID'
            )
                ->addColumn(
                    'tag_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    ],
                    'Tag ID'
                )
                ->addColumn(
                    'position',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'default'  => '0'
                    ],
                    'Position'
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_blog_post_tag', ['post_id']),
                    ['post_id']
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_blog_post_tag', ['tag_id']),
                    ['tag_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_blog_post_tag',
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
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_blog_post_tag',
                        'tag_id',
                        'mageplaza_blog_tag',
                        'tag_id'
                    ),
                    'tag_id',
                    $installer->getTable('mageplaza_blog_tag'),
                    'tag_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName(
                        'mageplaza_blog_post_tag',
                        [
                            'post_id',
                            'tag_id'
                        ],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'post_id',
                        'tag_id'
                    ],
                    [
                        'type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ]
                )
                ->setComment('Post To Tag Link Table');
            $installer->getConnection()->createTable($table);
        }
        if (! $installer->tableExists('mageplaza_blog_post_topic')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_blog_post_topic'));
            $table->addColumn(
                'post_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Post ID'
            )
                ->addColumn(
                    'topic_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    ],
                    'Topic ID'
                )
                ->addColumn(
                    'position',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'default'  => '0'
                    ],
                    'Position'
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_blog_post_topic', ['post_id']),
                    ['post_id']
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_blog_post_topic', ['topic_id']),
                    ['topic_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_blog_post_topic',
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
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_blog_post_topic',
                        'topic_id',
                        'mageplaza_blog_topic',
                        'topic_id'
                    ),
                    'topic_id',
                    $installer->getTable('mageplaza_blog_topic'),
                    'topic_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addIndex(
                    $installer->getIdxName(
                        'mageplaza_blog_post_topic',
                        [
                            'post_id',
                            'topic_id'
                        ],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'post_id',
                        'topic_id'
                    ],
                    [
                        'type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ]
                )
                ->setComment('Post To Topic Link Table');
            $installer->getConnection()->createTable($table);
        }
        if (! $installer->tableExists('mageplaza_blog_post_category')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_blog_post_category'));
            $table->addColumn(
                'category_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false,
                    'primary'  => true,
                ],
                'Category ID'
            )
                ->addColumn(
                    'post_id',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    ],
                    'Post ID'
                )
                ->addColumn(
                    'position',
                    \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'default'  => '0'
                    ],
                    'Position'
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_blog_post_category', ['category_id']),
                    ['category_id']
                )
                ->addIndex(
                    $installer->getIdxName('mageplaza_blog_post_category', ['post_id']),
                    ['post_id']
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_blog_post_category',
                        'category_id',
                        'mageplaza_blog_category',
                        'category_id'
                    ),
                    'category_id',
                    $installer->getTable('mageplaza_blog_category'),
                    'category_id',
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
                    \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
                )
                ->addForeignKey(
                    $installer->getFkName(
                        'mageplaza_blog_post_category',
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
                        'mageplaza_blog_post_category',
                        [
                            'category_id',
                            'post_id'
                        ],
                        \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ),
                    [
                        'category_id',
                        'post_id'
                    ],
                    [
                        'type' => \Magento\Framework\DB\Adapter\AdapterInterface::INDEX_TYPE_UNIQUE
                    ]
                )
                ->setComment('Category To Post Link Table');
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
					'display_name',
					\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
					255,
					[],
					'Display Name'
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

		if (! $installer->tableExists('mageplaza_blog_comment')) {
			$table = $installer->getConnection()
				->newTable($installer->getTable('mageplaza_blog_comment'));
			$table->addColumn(
				'comment_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				[
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
					'nullable' => false,
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
				\Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP,
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
			)->addForeignKey(
					$installer->getFkName(
						'mageplaza_blog_comment',
						'reply_id',
						'mageplaza_blog_comment_reply',
						'reply_id'
					),
					'reply_id',
					$installer->getTable('mageplaza_blog_comment_reply'),
					'reply_id',
					\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE,
					\Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
				);
			$installer->getConnection()->createTable($table);
		}

		if (! $installer->tableExists('mageplaza_blog_comment_reply')) {
			$table = $installer->getConnection()
				->newTable($installer->getTable('mageplaza_blog_comment_reply'));
			$table->addColumn(
				'reply_id',
				\Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
				null,
				[
					'unsigned' => true,
					'nullable' => false,
					'primary'  => true,
				],
				'Reply ID'
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
				'User Comment ID'
			)->addIndex(
				$installer->getIdxName('mageplaza_blog_comment_reply', ['reply_id']),
				['reply_id']
			)->addIndex(
				$installer->getIdxName('mageplaza_blog_comment_reply', ['comment_id']),
				['comment_id']
			)->addIndex(
				$installer->getIdxName('mageplaza_blog_comment_reply', ['entity_id']),
				['entity_id']
			)->addForeignKey(
				$installer->getFkName(
					'mageplaza_blog_comment_reply',
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
					'mageplaza_blog_comment_reply',
					'comment_id',
					'mageplaza_blog_comment',
					'comment_id'
				),
				'comment_id',
				$installer->getTable('mageplaza_blog_comment'),
				'comment_id',
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

        $installer->endSetup();
    }
}
