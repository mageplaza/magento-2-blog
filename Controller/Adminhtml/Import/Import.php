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

namespace Mageplaza\Blog\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Messages;
use Mageplaza\Blog\Helper\Data as BlogHelper;
use Mageplaza\Blog\Model\Import\AheadWorksM1;
use Mageplaza\Blog\Model\Import\MageFanM2;
use Mageplaza\Blog\Model\Import\WordPress;

/**
 * Class Import
 * @package Mageplaza\Blog\Controller\Adminhtml\Import
 */
class Import extends Action
{
    /**
     * @var WordPress
     */
    protected $_wordpressModel;

    /**
     * @var AheadWorksM1
     */
    protected $_aheadWorksM1Model;

    /**
     * @var MageFanM2
     */
    protected $_mageFanM2Model;

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
     *
     * @param Context $context
     * @param WordPress $wordPress
     * @param AheadWorksM1 $aheadWorksM1
     * @param MageFanM2 $mageFanM2
     * @param BlogHelper $blogHelper
     * @param Registry $registry
     */
    public function __construct(
        Action\Context $context,
        WordPress $wordPress,
        AheadWorksM1 $aheadWorksM1,
        MageFanM2 $mageFanM2,
        BlogHelper $blogHelper,
        Registry $registry
    ) {
        $this->blogHelper         = $blogHelper;
        $this->_wordpressModel    = $wordPress;
        $this->_aheadWorksM1Model = $aheadWorksM1;
        $this->_mageFanM2Model    = $mageFanM2;
        $this->registry           = $registry;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|mixed
     */
    public function execute()
    {
        $data = $this->_getSession()->getData('mageplaza_blog_import_data');
        switch ($data['type']) {
            case 'wordpress':
                $response = $this->processImport($this->_wordpressModel, $data);
                break;
            case 'aheadworksm1':
                $response = $this->processImport($this->_aheadWorksM1Model, $data);
                break;
            case 'magefan':
                $response = $this->processImport($this->_mageFanM2Model, $data);
                break;
            default:
                $response = $this->processImport($this->_wordpressModel, $data);
        }

        return $response;
    }

    /**
     * @param array $statisticData
     * @param Object $messagesBlock
     * @param array $data
     *
     * @return mixed
     */
    protected function getStatistic($statisticData, $messagesBlock, $data)
    {
        switch ($data['behaviour']) {
            case 'replace':
                $statisticHtml = $messagesBlock
                    ->{'addsuccess'}(__(
                        'You have replaced and updated %1 %2 successful. Skipped %3 %2.',
                        $statisticData['success_count'],
                        $statisticData['type'],
                        $statisticData['error_count']
                    ))
                    ->toHtml();
                break;

            case 'delete':
                $statisticHtml = $messagesBlock
                    ->{'addsuccess'}(__(
                        'You have deleted %1 %2 successful. Skipped %3 %2.',
                        $statisticData['success_count'],
                        $statisticData['type'],
                        $statisticData['error_count']
                    ))
                    ->toHtml();
                break;
            default:
                $statisticHtml = $messagesBlock
                    ->{'addsuccess'}(__(
                        'You have imported %1 %2 successful. Skipped %3 %2.',
                        $statisticData['success_count'],
                        $statisticData['type'],
                        $statisticData['error_count']
                    ))
                    ->toHtml();
        }

        return $statisticHtml;
    }

    /**
     * @param Object $object
     * @param array $data
     *
     * @return mixed
     */
    protected function processImport($object, $data)
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $statisticHtml = '';
        $connection    = mysqli_connect($data['host'], $data['user_name'], $data['password'], $data['database']);
        $messagesBlock = $this->_view->getLayout()->createBlock(Messages::class);
        if ($object->run($data, $connection)) {
            $postStatistic = $this->registry->registry('mageplaza_import_post_statistic');
            if ($postStatistic && $postStatistic['has_data']) {
                $statisticHtml = $this->getStatistic($postStatistic, $messagesBlock, $data);
            }

            $tagStatistic = $this->registry->registry('mageplaza_import_tag_statistic');
            if ($tagStatistic && $tagStatistic['has_data']) {
                $statisticHtml = $this->getStatistic($tagStatistic, $messagesBlock, $data);
            }

            $categoryStatistic = $this->registry->registry('mageplaza_import_category_statistic');
            if ($categoryStatistic && $categoryStatistic['has_data']) {
                $statisticHtml = $this->getStatistic($categoryStatistic, $messagesBlock, $data);
            }

            $authorStatistic = $this->registry->registry('mageplaza_import_user_statistic');
            if ($authorStatistic && $authorStatistic['has_data']) {
                $statisticHtml = $this->getStatistic($authorStatistic, $messagesBlock, $data);
            }

            $commentStatistic = $this->registry->registry('mageplaza_import_comment_statistic');
            if ($commentStatistic && $commentStatistic['has_data']) {
                $statisticHtml = $this->getStatistic($commentStatistic, $messagesBlock, $data);
            }

            if ($statisticHtml == '' && $data['behaviour'] == 'update') {
                $statisticHtml = $messagesBlock
                    ->{'addsuccess'}(__('There are no records are updated.'))
                    ->toHtml();
            }

            $result = ['statistic' => $statisticHtml, 'status' => 'ok'];
            mysqli_close($connection);

            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        } else {
            $statisticHtml = $messagesBlock
                ->{'adderror'}(__('Can not make import, please check your table prefix OR import type and try again.'))
                ->toHtml();
            $result        = ['statistic' => $statisticHtml, 'status' => 'ok'];

            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        }
    }
}
