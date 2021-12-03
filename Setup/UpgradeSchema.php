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

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Mageplaza\Blog\Helper\Data;
use Zend_Db_Exception;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * InstallSchema constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if (version_compare($context->getVersion(), '1.1.1', '<')) {
            if ($installer->tableExists('mageplaza_blog_tag')) {
                $columns = [
                    'meta_title'       => [
                        'type'    => Table::TYPE_TEXT,
                        'length'  => 255,
                        'comment' => 'Post Meta Title',
                    ],
                    'meta_description' => [
                        'type'    => Table::TYPE_TEXT,
                        'length'  => '64k',
                        'comment' => 'Post Meta Description',
                    ],
                    'meta_keywords'    => [
                        'type'    => Table::TYPE_TEXT,
                        'length'  => '64k',
                        'comment' => 'Post Meta Keywords',
                    ],
                    'meta_robots'      => [
                        'type'    => Table::TYPE_INTEGER,
                        'length'  => '64k',
                        'comment' => 'Post Meta Robots',
                    ]
                ];

                $tagTable = $installer->getTable('mageplaza_blog_tag');
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($tagTable, $name, $definition);
                }
            }

            if (!$installer->tableExists('mageplaza_blog_post_traffic')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_blog_post_traffic'))
                    ->addColumn('traffic_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ], 'Traffic ID')
                    ->addColumn('post_id', Table::TYPE_INTEGER, null, [
                        'unsigned' => true,
                        'nullable' => false
                    ], 'Post ID')
                    ->addColumn('numbers_view', Table::TYPE_TEXT, 255, [], 'Numbers View')
                    ->addIndex($installer->getIdxName('mageplaza_blog_post_traffic', ['post_id']), ['post_id'])
                    ->addIndex(
                        $installer->getIdxName(
                            'mageplaza_blog_post_traffic',
                            ['traffic_id']
                        ),
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
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            'mageplaza_blog_post_traffic',
                            ['post_id', 'traffic_id'],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['post_id', 'traffic_id'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Traffic Post Table');

                $installer->getConnection()->createTable($table);
            }
            if (!$installer->tableExists('mageplaza_blog_author')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_blog_author'))
                    ->addColumn('user_id', Table::TYPE_INTEGER, null, [
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    ], 'User ID')
                    ->addColumn('name', Table::TYPE_TEXT, 255, [], 'Display Name')
                    ->addColumn('url_key', Table::TYPE_TEXT, 255, [], 'Author URL Key')
                    ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Author Updated At')
                    ->addColumn(
                        'created_at',
                        Table::TYPE_TIMESTAMP,
                        null,
                        ['default' => Table::TIMESTAMP_INIT],
                        'Author Created At'
                    )
                    ->addForeignKey(
                        $installer->getFkName('mageplaza_blog_author', 'user_id', 'admin_user', 'user_id'),
                        'user_id',
                        $installer->getTable('admin_user'),
                        'user_id',
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            'mageplaza_blog_author',
                            ['user_id'],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['user_id'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Author Table');

                $installer->getConnection()->createTable($table);
            }

            if ($installer->tableExists('mageplaza_blog_topic')) {
                $columns = [
                    'author_id'   => [
                        'type'     => Table::TYPE_INTEGER,
                        'comment'  => 'Author ID',
                        'unsigned' => true,
                    ],
                    'modifier_id' => [
                        'type'     => Table::TYPE_INTEGER,
                        'comment'  => 'Modifier ID',
                        'unsigned' => true,
                    ],
                ];

                $table = $installer->getTable('mageplaza_blog_post');
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($table, $name, $definition);
                }
            }
        }
        if (version_compare($context->getVersion(), '1.1.2', '<')) {
            if ($installer->tableExists('mageplaza_blog_post')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_post'),
                    'meta_robots',
                    ['type' => Table::TYPE_TEXT]
                );
            }
            if ($installer->tableExists('mageplaza_blog_tag')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_tag'),
                    'meta_robots',
                    ['type' => Table::TYPE_TEXT]
                );
            }
            if ($installer->tableExists('mageplaza_blog_category')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_category'),
                    'meta_robots',
                    ['type' => Table::TYPE_TEXT]
                );
            }
            if ($installer->tableExists('mageplaza_blog_topic')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_topic'),
                    'meta_robots',
                    ['type' => Table::TYPE_TEXT]
                );
            }

            if (!$installer->tableExists('mageplaza_blog_comment')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_blog_comment'))
                    ->addColumn('comment_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    ], 'Comment ID')
                    ->addColumn(
                        'post_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false,],
                        'Post ID'
                    )
                    ->addColumn(
                        'entity_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false,],
                        'User Comment ID'
                    )
                    ->addColumn(
                        'has_reply',
                        Table::TYPE_SMALLINT,
                        2,
                        ['unsigned' => true, 'nullable' => false, 'default' => 0],
                        'Comment has reply'
                    )
                    ->addColumn(
                        'is_reply',
                        Table::TYPE_SMALLINT,
                        2,
                        ['unsigned' => true, 'nullable' => false, 'default' => 0],
                        'Is reply comment'
                    )
                    ->addColumn(
                        'reply_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => true, 'default' => 0],
                        'Reply ID'
                    )
                    ->addColumn('content', Table::TYPE_TEXT, 255, [], 'Comment content')
                    ->addColumn('created_at', Table::TYPE_TEXT, null, [], 'Comment Created At')
                    ->addColumn(
                        'status',
                        Table::TYPE_SMALLINT,
                        3,
                        ['unsigned' => true, 'nullable' => false, 'default' => 3],
                        'Status'
                    )
                    ->addColumn(
                        'store_ids',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => false, 'unsigned' => true,],
                        'Store Id'
                    )
                    ->addIndex($installer->getIdxName('mageplaza_blog_comment', ['comment_id']), ['comment_id'])
                    ->addIndex($installer->getIdxName('mageplaza_blog_comment', ['entity_id']), ['entity_id'])
                    ->addForeignKey(
                        $installer->getFkName(
                            'mageplaza_blog_comment',
                            'entity_id',
                            'customer_entity',
                            'entity_id'
                        ),
                        'entity_id',
                        $installer->getTable('customer_entity'),
                        'entity_id',
                        Table::ACTION_CASCADE
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
                        Table::ACTION_CASCADE
                    );

                $installer->getConnection()->createTable($table);
            }
            if (!$installer->tableExists('mageplaza_blog_comment_like')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_blog_comment_like'))
                    ->addColumn('like_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    ], 'Like ID')
                    ->addColumn(
                        'comment_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false,],
                        'Comment ID'
                    )
                    ->addColumn(
                        'entity_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false,],
                        'User Like ID'
                    )
                    ->addIndex($installer->getIdxName('mageplaza_blog_comment_like', ['like_id']), ['like_id'])
                    ->addForeignKey(
                        $installer->getFkName(
                            'mageplaza_blog_comment_like',
                            'comment_id',
                            'mageplaza_blog_comment',
                            'comment_id'
                        ),
                        'comment_id',
                        $installer->getTable('mageplaza_blog_comment'),
                        'comment_id',
                        Table::ACTION_CASCADE
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
                        Table::ACTION_CASCADE
                    );

                $installer->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '1.1.3', '<')) {
            if ($installer->tableExists('mageplaza_blog_author')) {
                $columns = [
                    'image'             => [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 255,
                        'comment'  => 'Author Image',
                        'unsigned' => true,
                    ],
                    'short_description' => [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => '64k',
                        'comment'  => 'Author Short Description',
                        'unsigned' => true,
                    ],
                    'facebook_link'     => [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 255,
                        'comment'  => 'Facebook Link',
                        'unsigned' => true,
                    ],
                    'twitter_link'      => [
                        'type'     => Table::TYPE_TEXT,
                        'length'   => 255,
                        'comment'  => 'Twitter Link',
                        'unsigned' => true,
                    ],
                ];

                $table = $installer->getTable('mageplaza_blog_author');
                foreach ($columns as $name => $definition) {
                    $connection->addColumn($table, $name, $definition);
                }
            }
        }

        if (version_compare($context->getVersion(), '1.1.4', '<')) {
            if (!$installer->tableExists('mageplaza_blog_post_product')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_blog_post_product'))
                    ->addColumn('post_id', Table::TYPE_INTEGER, null, [
                        'unsigned' => true,
                        'primary'  => true,
                        'nullable' => false
                    ], 'Post ID')
                    ->addColumn('entity_id', Table::TYPE_INTEGER, null, [
                        'unsigned' => true,
                        'primary'  => true,
                        'nullable' => false
                    ], 'Entity ID')
                    ->addColumn(
                        'position',
                        Table::TYPE_INTEGER,
                        null,
                        ['nullable' => false, 'default' => '0'],
                        'Position'
                    )
                    ->addIndex($installer->getIdxName('mageplaza_blog_post_product', ['post_id']), ['post_id'])
                    ->addIndex($installer->getIdxName('mageplaza_blog_post_product', ['entity_id']), ['entity_id'])
                    ->addForeignKey(
                        $installer->getFkName(
                            'mageplaza_blog_post_product',
                            'post_id',
                            'mageplaza_blog_post',
                            'post_id'
                        ),
                        'post_id',
                        $installer->getTable('mageplaza_blog_post'),
                        'post_id',
                        Table::ACTION_CASCADE
                    )
                    ->addForeignKey(
                        $installer->getFkName(
                            'mageplaza_blog_post_product',
                            'entity_id',
                            'catalog_product_entity',
                            'entity_id'
                        ),
                        'entity_id',
                        $installer->getTable('catalog_product_entity'),
                        'entity_id',
                        Table::ACTION_CASCADE
                    )
                    ->addIndex(
                        $installer->getIdxName(
                            'mageplaza_blog_post_product',
                            ['post_id', 'entity_id'],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['post_id', 'entity_id'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Post To Product Link Table');

                $installer->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '2.4.2', '<')) {
            if ($installer->tableExists('mageplaza_blog_post')) {
                $connection->addColumn($installer->getTable('mageplaza_blog_post'), 'publish_date', [
                    'type'    => Table::TYPE_TIMESTAMP,
                    null,
                    'comment' => 'Post Publish Date',
                ]);
            }
        }

        if (version_compare($context->getVersion(), '2.4.3', '<')) {
            if ($installer->tableExists('mageplaza_blog_post')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_post'),
                    'created_at',
                    ['type' => Table::TYPE_DATETIME]
                );
            }
            if ($installer->tableExists('mageplaza_blog_post')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_post'),
                    'updated_at',
                    ['type' => Table::TYPE_DATETIME]
                );
            }
            if ($installer->tableExists('mageplaza_blog_post')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_post'),
                    'publish_date',
                    ['type' => Table::TYPE_DATETIME]
                );
            }
            if ($installer->tableExists('mageplaza_blog_tag')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_tag'),
                    'created_at',
                    ['type' => Table::TYPE_DATETIME]
                );
            }
            if ($installer->tableExists('mageplaza_blog_tag')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_tag'),
                    'updated_at',
                    ['type' => Table::TYPE_DATETIME]
                );
            }
            if ($installer->tableExists('mageplaza_blog_category')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_category'),
                    'created_at',
                    ['type' => Table::TYPE_DATETIME]
                );
            }
            if ($installer->tableExists('mageplaza_blog_category')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_category'),
                    'updated_at',
                    ['type' => Table::TYPE_DATETIME]
                );
            }
            if ($installer->tableExists('mageplaza_blog_topic')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_topic'),
                    'created_at',
                    ['type' => Table::TYPE_DATETIME]
                );
            }
            if ($installer->tableExists('mageplaza_blog_topic')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_topic'),
                    'updated_at',
                    ['type' => Table::TYPE_DATETIME]
                );
            }
        }

        if (version_compare($context->getVersion(), '2.4.4', '<')) {
            if ($installer->tableExists('mageplaza_blog_comment')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_comment'),
                    'content',
                    ['type' => Table::TYPE_TEXT]
                );
            }
            if ($installer->tableExists('mageplaza_blog_comment')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_comment'),
                    'created_at',
                    ['type' => Table::TYPE_DATETIME]
                );
            }

            if ($installer->tableExists('mageplaza_blog_comment')) {
                if (!$connection->tableColumnExists($installer->getTable('mageplaza_blog_comment'), 'status')) {
                    $connection->addColumn($installer->getTable('mageplaza_blog_comment'), 'status', [
                        'type'    => Table::TYPE_INTEGER,
                        3,
                        ['unsigned' => true, 'nullable' => false, 'default' => 3],
                        'comment' => 'status',
                    ]);
                }
                if (!$connection->tableColumnExists($installer->getTable('mageplaza_blog_comment'), 'store_ids')) {
                    $connection->addColumn($installer->getTable('mageplaza_blog_comment'), 'store_ids', [
                        'type'    => Table::TYPE_TEXT,
                        null,
                        ['nullable' => false, 'unsigned' => true,],
                        'comment' => 'Store Id',
                    ]);
                }
            }
        }

        if (version_compare($context->getVersion(), '2.4.5', '<')) {
            if ($installer->tableExists('mageplaza_blog_post_traffic')) {
                $connection->modifyColumn(
                    $installer->getTable('mageplaza_blog_post_traffic'),
                    'numbers_view',
                    ['type' => Table::TYPE_INTEGER]
                );
            }
        }

        if (version_compare($context->getVersion(), '2.4.6', '<')) {
            if ($installer->tableExists('mageplaza_blog_comment')) {
                $connection->dropForeignKey(
                    $installer->getTable('mageplaza_blog_comment'),
                    $installer->getFkName('mageplaza_blog_comment', 'entity_id', 'customer_entity', 'entity_id')
                );
            }
        }

        if (version_compare($context->getVersion(), '2.4.7', '<')) {
            if ($installer->tableExists('mageplaza_blog_comment')) {
                if (!$connection->tableColumnExists($installer->getTable('mageplaza_blog_comment'), 'user_name')) {
                    $connection->addColumn($installer->getTable('mageplaza_blog_comment'), 'user_name', [
                        'type'    => Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => true],
                        'comment' => 'User Name',
                    ]);
                }
                if (!$connection->tableColumnExists($installer->getTable('mageplaza_blog_comment'), 'user_email')) {
                    $connection->addColumn($installer->getTable('mageplaza_blog_comment'), 'user_email', [
                        'type'    => Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => true],
                        'comment' => 'User Email',
                    ]);
                }
            }
        }

        if (version_compare($context->getVersion(), '2.4.8', '<')) {
            if ($installer->tableExists('mageplaza_blog_post')) {
                if (!$connection->tableColumnExists($installer->getTable('mageplaza_blog_post'), 'import_source')) {
                    $connection->addColumn($installer->getTable('mageplaza_blog_post'), 'import_source', [
                        'type'    => Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => true],
                        'comment' => 'Import Source',
                    ]);
                }
            }

            if ($installer->tableExists('mageplaza_blog_tag')) {
                if (!$connection->tableColumnExists($installer->getTable('mageplaza_blog_tag'), 'import_source')) {
                    $connection->addColumn($installer->getTable('mageplaza_blog_tag'), 'import_source', [
                        'type'    => Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => true],
                        'comment' => 'Import Source',
                    ]);
                }
            }

            if ($installer->tableExists('mageplaza_blog_category')) {
                if (!$connection->tableColumnExists(
                    $installer->getTable('mageplaza_blog_category'),
                    'import_source'
                )
                ) {
                    $connection->addColumn($installer->getTable('mageplaza_blog_category'), 'import_source', [
                        'type'    => Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => true],
                        'comment' => 'Import Source',
                    ]);
                }
            }

            if ($installer->tableExists('mageplaza_blog_comment')) {
                if (!$connection->tableColumnExists(
                    $installer->getTable('mageplaza_blog_comment'),
                    'import_source'
                )
                ) {
                    $connection->addColumn($installer->getTable('mageplaza_blog_comment'), 'import_source', [
                        'type'    => Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => true],
                        'comment' => 'Import Source',
                    ]);
                }
            }

            if ($installer->tableExists('mageplaza_blog_topic')) {
                if (!$connection->tableColumnExists(
                    $installer->getTable('mageplaza_blog_topic'),
                    'import_source'
                )
                ) {
                    $connection->addColumn($installer->getTable('mageplaza_blog_topic'), 'import_source', [
                        'type'    => Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => true],
                        'comment' => 'Import Source',
                    ]);
                }
            }
        }

        if (version_compare($context->getVersion(), '2.4.9', '<')) {
            if ($installer->tableExists('mageplaza_blog_post')) {
                if ($connection->tableColumnExists($installer->getTable('mageplaza_blog_post'), 'created_at')) {
                    $connection->modifyColumn(
                        $installer->getTable('mageplaza_blog_post'),
                        'created_at',
                        ['type' => Table::TYPE_TIMESTAMP]
                    );
                }
                if ($connection->tableColumnExists($installer->getTable('mageplaza_blog_post'), 'updated_at')) {
                    $connection->modifyColumn(
                        $installer->getTable('mageplaza_blog_post'),
                        'updated_at',
                        ['type' => Table::TYPE_TIMESTAMP]
                    );
                }
                if ($connection->tableColumnExists($installer->getTable('mageplaza_blog_post'), 'publish_date')) {
                    $connection->modifyColumn(
                        $installer->getTable('mageplaza_blog_post'),
                        'publish_date',
                        ['type' => Table::TYPE_TIMESTAMP]
                    );
                }
            }
            if ($installer->tableExists('mageplaza_blog_category')) {
                if ($connection->tableColumnExists($installer->getTable('mageplaza_blog_category'), 'created_at')) {
                    $connection->modifyColumn(
                        $installer->getTable('mageplaza_blog_category'),
                        'created_at',
                        ['type' => Table::TYPE_TIMESTAMP]
                    );
                }
                if ($connection->tableColumnExists($installer->getTable('mageplaza_blog_category'), 'updated_at')) {
                    $connection->modifyColumn(
                        $installer->getTable('mageplaza_blog_category'),
                        'updated_at',
                        ['type' => Table::TYPE_TIMESTAMP]
                    );
                }
            }
            if ($installer->tableExists('mageplaza_blog_tag')) {
                if ($connection->tableColumnExists($installer->getTable('mageplaza_blog_tag'), 'created_at')) {
                    $connection->modifyColumn(
                        $installer->getTable('mageplaza_blog_tag'),
                        'created_at',
                        ['type' => Table::TYPE_TIMESTAMP]
                    );
                }
                if ($connection->tableColumnExists($installer->getTable('mageplaza_blog_tag'), 'updated_at')) {
                    $connection->modifyColumn(
                        $installer->getTable('mageplaza_blog_tag'),
                        'updated_at',
                        ['type' => Table::TYPE_TIMESTAMP]
                    );
                }
            }
            if ($installer->tableExists('mageplaza_blog_topic')) {
                if ($connection->tableColumnExists($installer->getTable('mageplaza_blog_topic'), 'created_at')) {
                    $connection->modifyColumn(
                        $installer->getTable('mageplaza_blog_topic'),
                        'created_at',
                        ['type' => Table::TYPE_TIMESTAMP]
                    );
                }
                if ($connection->tableColumnExists($installer->getTable('mageplaza_blog_topic'), 'updated_at')) {
                    $connection->modifyColumn(
                        $installer->getTable('mageplaza_blog_topic'),
                        'updated_at',
                        ['type' => Table::TYPE_TIMESTAMP]
                    );
                }
            }
            if ($installer->tableExists('mageplaza_blog_comment')) {
                if ($connection->tableColumnExists($installer->getTable('mageplaza_blog_comment'), 'created_at')) {
                    $connection->modifyColumn(
                        $installer->getTable('mageplaza_blog_comment'),
                        'created_at',
                        ['type' => Table::TYPE_TIMESTAMP]
                    );
                }
            }
        }

        if (version_compare($context->getVersion(), '2.5.0', '<')) {
            if ($installer->tableExists('mageplaza_blog_post')) {
                if (!$connection->tableColumnExists($installer->getTable('mageplaza_blog_post'), 'layout')) {
                    $connection->addColumn($installer->getTable('mageplaza_blog_post'), 'layout', [
                        'type'    => Table::TYPE_TEXT,
                        null,
                        ['unsigned' => true, 'nullable' => true],
                        'comment' => 'Post Layout',
                    ]);
                }
            }
        }

        if (version_compare($context->getVersion(), '2.5.1', '<')) {
            if ($installer->tableExists('mageplaza_blog_author')) {
                $connection->changeColumn(
                    $installer->getTable('mageplaza_blog_author'),
                    'user_id',
                    'user_id',
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'size'     => null,
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                        'comment'  => 'Author ID'
                    ]
                );

                $connection->addColumn(
                    $installer->getTable('mageplaza_blog_author'),
                    'status',
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'nullable' => true,
                        'default'  => 0,
                        'comment'  => 'Author Status',
                        'after'    => 'url_key'
                    ]
                );

                $connection->addColumn(
                    $installer->getTable('mageplaza_blog_author'),
                    'type',
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'nullable' => true,
                        'default'  => 0,
                        'comment'  => 'Author Type',
                        'after'    => 'url_key'
                    ]
                );

                $connection->addColumn(
                    $installer->getTable('mageplaza_blog_author'),
                    'customer_id',
                    [
                        'type'     => Table::TYPE_INTEGER,
                        'unsigned' => true,
                        'nullable' => true,
                        'default'  => 0,
                        'comment'  => 'Customer ID',
                        'after'    => 'url_key'
                    ]
                );

                $connection->dropForeignKey(
                    $installer->getTable('mageplaza_blog_author'),
                    $installer->getFkName('mageplaza_blog_author', 'user_id', 'admin_user', 'user_id')
                );
            }
            if ($installer->tableExists('mageplaza_blog_author')
                && $installer->tableExists('mageplaza_blog_post')) {
                $connection->addForeignKey(
                    $installer->getFkName('mageplaza_blog_post', 'author_id', 'mageplaza_blog_author', 'user_id'),
                    $installer->getTable('mageplaza_blog_post'),
                    'author_id',
                    $installer->getTable('mageplaza_blog_author'),
                    'user_id',
                    Table::ACTION_CASCADE
                );
            }
        }

        if (version_compare($context->getVersion(), '2.5.2', '<')) {
            if (!$installer->tableExists('mageplaza_blog_post_like')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_blog_post_like'))
                    ->addColumn('like_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary'  => true,
                    ], 'Like ID')
                    ->addColumn(
                        'post_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false,],
                        'Post ID'
                    )
                    ->addColumn(
                        'action',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false,],
                        'type like'
                    )
                    ->addColumn(
                        'entity_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false,],
                        'User Like ID'
                    )
                    ->addIndex($installer->getIdxName('mageplaza_blog_post_like', ['like_id']), ['like_id'])
                    ->addForeignKey(
                        $installer->getFkName(
                            'mageplaza_blog_post_like',
                            'post_id',
                            'mageplaza_blog_post',
                            'post_id'
                        ),
                        'post_id',
                        $installer->getTable('mageplaza_blog_post'),
                        'post_id',
                        Table::ACTION_CASCADE
                    );

                $installer->getConnection()->createTable($table);
            }
            if (!$installer->tableExists('mageplaza_blog_post_history')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_blog_post_history'))
                    ->addColumn('history_id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ], 'History ID')
                    ->addColumn(
                        'post_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false,],
                        'Post ID'
                    )
                    ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable => false'], 'Post Name')
                    ->addColumn('short_description', Table::TYPE_TEXT, '64k', [], 'Post Short Description')
                    ->addColumn('post_content', Table::TYPE_TEXT, '64k', [], 'Post Content')
                    ->addColumn(
                        'store_ids',
                        Table::TYPE_TEXT,
                        null,
                        [
                            'nullable' => false,
                            'unsigned' => true
                        ],
                        'Store Id'
                    )
                    ->addColumn('image', Table::TYPE_TEXT, 255, [], 'Post Image')
                    ->addColumn('views', Table::TYPE_INTEGER, null, [], 'Post Views')
                    ->addColumn('enabled', Table::TYPE_INTEGER, 1, [], 'Post Enabled')
                    ->addColumn('url_key', Table::TYPE_TEXT, 255, [], 'Post URL Key')
                    ->addColumn('in_rss', Table::TYPE_INTEGER, 1, [], 'Post In RSS')
                    ->addColumn('allow_comment', Table::TYPE_INTEGER, 1, [], 'Post Allow Comment')
                    ->addColumn('meta_title', Table::TYPE_TEXT, 255, [], 'Post Meta Title')
                    ->addColumn('meta_description', Table::TYPE_TEXT, '64k', [], 'Post Meta Description')
                    ->addColumn('meta_keywords', Table::TYPE_TEXT, '64k', [], 'Post Meta Keywords')
                    ->addColumn('meta_robots', Table::TYPE_TEXT, '64k', [], 'Post Meta Robots')
                    ->addColumn('updated_at', Table::TYPE_TIMESTAMP, null, [], 'Post Updated At')
                    ->addColumn('created_at', Table::TYPE_TIMESTAMP, null, [], 'Post Created At')
                    ->addColumn(
                        'author_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false,],
                        'Author ID'
                    )
                    ->addColumn(
                        'modifier_id',
                        Table::TYPE_INTEGER,
                        null,
                        ['unsigned' => true, 'nullable' => false,],
                        'Modifier ID'
                    )
                    ->addColumn('publish_date', Table::TYPE_TIMESTAMP, null, [], 'Publish Date')
                    ->addColumn(
                        'import_source',
                        Table::TYPE_TEXT,
                        '64k',
                        ['nullable' => true],
                        'Import Source'
                    )
                    ->addColumn(
                        'layout',
                        Table::TYPE_TEXT,
                        '64k',
                        ['nullable' => true],
                        'Post Layout'
                    )
                    ->addColumn(
                        'category_ids',
                        Table::TYPE_TEXT,
                        '255',
                        ['nullable' => true],
                        'Post Category Id'
                    )
                    ->addColumn(
                        'tag_ids',
                        Table::TYPE_TEXT,
                        '255',
                        ['nullable' => true],
                        'Post Tag Id'
                    )
                    ->addColumn(
                        'topic_ids',
                        Table::TYPE_TEXT,
                        '255',
                        ['nullable' => true],
                        'Post Topic Id'
                    )
                    ->addColumn(
                        'product_ids',
                        Table::TYPE_TEXT,
                        '64k',
                        ['nullable' => true],
                        'Post Product Id'
                    )
                    ->setComment('Post History Table');

                $installer->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '2.5.3', '<')
            && $installer->tableExists('mageplaza_blog_author')
        ) {
            $connection->addColumn(
                $installer->getTable('mageplaza_blog_author'),
                'email',
                [
                    'type'     => Table::TYPE_TEXT,
                    'nullable' => true,
                    'default'  => '',
                    'comment'  => 'Author email',
                    'after'    => 'url_key'
                ]
            );
        }

        $installer->endSetup();
    }
}
