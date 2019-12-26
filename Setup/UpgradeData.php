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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Model\AuthorFactory;
use Mageplaza\Blog\Model\CommentFactory;

/**
 * Class UpgradeData
 * @package Mageplaza\Blog\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * Date model
     *
     * @var DateTime
     */
    public $date;

    /**
     * @var CommentFactory
     */
    public $comment;

    /**
     * @var AuthorFactory
     */
    public $author;

    /**
     * UpgradeData constructor.
     *
     * @param DateTime $date
     * @param CommentFactory $commentFactory
     * @param AuthorFactory $authorFactory
     */
    public function __construct(
        DateTime $date,
        CommentFactory $commentFactory,
        AuthorFactory $authorFactory
    ) {
        $this->comment = $commentFactory;
        $this->author  = $authorFactory;
        $this->date    = $date;
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
                $setup->getConnection()->update(
                    $setup->getTable('mageplaza_blog_comment'),
                    $sampleTemplates,
                    'comment_id IN (' . $commentIds . ')'
                );
            }
        }

        if (version_compare($context->getVersion(), '2.5.2', '<')) {
            if ($this->author->create()->getCollection()->count() < 1) {
                $this->author->create()->addData(
                    [
                        'name'       => 'Admin',
                        'type'       => 0,
                        'status'     => 1,
                        'created_at' => $this->date->date()
                    ]
                )->save();
            }
        }
        $installer->endSetup();
    }
}
