<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mageplaza\Blog\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Model\CommentFactory;

class UpgradeData implements UpgradeDataInterface
{

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
     * UpgradeData constructor.
     * @param DateTime $date
     * @param CommentFactory $commentFactory
     */
    public function __construct(
        DateTime $date,
        CommentFactory $commentFactory
    )
    {
        $this->comment = $commentFactory;
        $this->date = $date;
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
                    'status' => 3
                ];
                $setup->getConnection()->update($setup->getTable('mageplaza_blog_comment'), $sampleTemplates, 'comment_id IN (' . $commentIds . ')');
            }
            $installer->endSetup();
        }
    }

}
