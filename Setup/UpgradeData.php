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

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Mageplaza\Blog\Model\AuthorFactory;
use Mageplaza\Blog\Model\Author;
use Mageplaza\Blog\Model\CategoryFactory;
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
    protected $date;

    /**
     * @var CommentFactory
     */
    protected $comment;

    /**
     * @var AuthorFactory
     */
    protected $author;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * UpgradeData constructor.
     *
     * @param DateTime $date
     * @param CommentFactory $commentFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param AuthorFactory $authorFactory
     * @param CategoryFactory $categoryFactory
     */
    public function __construct(
        DateTime $date,
        CommentFactory $commentFactory,
        CustomerRepositoryInterface $customerRepository,
        AuthorFactory $authorFactory,
        CategoryFactory $categoryFactory
    ) {
        $this->comment            = $commentFactory;
        $this->author             = $authorFactory;
        $this->date               = $date;
        $this->customerRepository = $customerRepository;
        $this->categoryFactory    = $categoryFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Exception
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

        if (!$this->author->create()->getCollection()->getSize()) {
            $this->author->create()->addData(
                [
                    'name'       => 'Admin',
                    'type'       => 0,
                    'status'     => 1,
                    'created_at' => $this->date->date()
                ]
            )->save();
        }

        if (!$this->categoryFactory->create()->getCollection()->getSize()) {
            $this->categoryFactory->create()->addData(
                [
                    'path'           => '1',
                    'position'       => 0,
                    'children_count' => 0,
                    'level'          => 0,
                    'name'           => 'ROOT',
                    'url_key'        => 'root'
                ]
            )->save();
        }

        if (version_compare($context->getVersion(), '2.5.3', '<')) {
            /** @var Author $author */
            foreach ($this->author->create()->getCollection()->getItems() as $author) {
                if ($customerId = $author->getCustomerId()) {
                    try {
                        $author->setEmail($this->customerRepository->getById($customerId)->getEmail());
                        $author->save();
                    } catch (Exception $e) {
                        continue;
                    }
                }
            }
        }

        $installer->endSetup();
    }
}
