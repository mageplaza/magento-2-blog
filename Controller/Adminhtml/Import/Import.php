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
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mageplaza\Blog\Model\Import\WordPress;
use Mageplaza\Blog\Helper\Data as BlogHelper;

/**
 * Class Import
 * @package Mageplaza\Blog\Controller\Adminhtml\Import
 */
class Import extends Action
{
    /**
     * @var WordPress
     */
    public $importModel;

    /**
     * @var BlogHelper
     */
    public $blogHelper;

    /**
     * @var Registry
     */
    public $registry;

    /**
     * Import constructor.
     * @param Context $context
     * @param WordPress $wordPress
     * @param BlogHelper $blogHelper
     * @param Registry $registry
     */
    public function __construct(
        Action\Context $context,
        WordPress $wordPress,
        BlogHelper $blogHelper,
        Registry $registry
    )
    {
        $this->blogHelper = $blogHelper;
        $this->importModel = $wordPress;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->_getSession()->getData('mageplaza_blog_import_data');
        $statisticHtml = '';
        $connection = mysqli_connect($data["host"], $data["user_name"], $data["password"], $data["database"]);
        $this->importModel->runImport($data, $connection);
        $messagesBlock = $this->_view->getLayout()->createBlock(\Magento\Framework\View\Element\Messages::class);
        $postStatistic = $this->registry->registry('mageplaza_import_post_statistic');
        if ($postStatistic["has_data"]) {
            $statisticHtml = $this->getStatistic($postStatistic, $messagesBlock);
        }

        $tagStatistic = $this->registry->registry('mageplaza_import_tag_statistic');

        if ($tagStatistic["has_data"]) {
            $statisticHtml = $this->getStatistic($tagStatistic, $messagesBlock);
        }
        $categoryStatistic = $this->registry->registry('mageplaza_import_category_statistic');

        if ($categoryStatistic["has_data"]) {
            $statisticHtml = $this->getStatistic($categoryStatistic, $messagesBlock);
        }
        $result = ['statistic' => $statisticHtml, 'status' => 'ok'];
        mysqli_close($connection);
        return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
    }

    /**
     * @param $data
     * @param $messagesBlock
     * @return mixed
     */
    public function getStatistic($data, $messagesBlock)
    {

        if ($data["delete_count"] > 0) {
            $statisticHtml = $messagesBlock
                ->{'addsuccess'}(__('You have imported %1 %2 successful. Replaced %4 %2. Skipped %3 %2.',
                    $data['success_count'],
                    $data['type'],
                    $data['error_count'],
                    $data['delete_count']
                ))
                ->toHtml();
        } elseif ($data["success_count"] > 0) {
            $statisticHtml = $messagesBlock
                ->{'addsuccess'}(__('You have imported %1 %2 successful. Skipped %3 %2.',
                    $data['success_count'],
                    $data['type'],
                    $data['error_count']
                ))
                ->toHtml();
        } else {
            $statisticHtml = $messagesBlock
                ->{'adderror'}(__('There are something wrong while importing %2. Skipped %3 %2.',
                    $data['success_count'],
                    $data['type'],
                    $data['error_count']
                ))
                ->toHtml();
        }

        return $statisticHtml;
    }
}
