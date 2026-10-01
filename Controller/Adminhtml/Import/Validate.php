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

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Mageplaza\Blog\Helper\Data as BlogHelper;
use RuntimeException;

/**
 * Class Validate
 * @package Mageplaza\Blog\Controller\Adminhtml\Import
 */
class Validate extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Mageplaza_Blog::import';

    /**
     * @var BlogHelper
     */
    public $blogHelper;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * Validate constructor.
     *
     * @param Context $context
     * @param BlogHelper $blogHelper
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        Context $context,
        BlogHelper $blogHelper,
        EncryptorInterface $encryptor
    ) {
        $this->blogHelper = $blogHelper;
        $this->encryptor  = $encryptor;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        // phpcs:disable Magento2.Functions.DiscouragedFunction
        $data = $this->getRequest()->getParams();

        try {
            $host = (string) ($data['host'] ?? '');
            if (BlogHelper::isBlockedImportHost($host)) {
                $result = ['import_name' => $data['import_name'] ?? '', 'status' => 'false'];

                return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
            }

            $connect    = mysqli_connect($host, $data['user_name'], $data['password'], $data['database']);
            $importName = $data['import_name'];

            $sessionData = $data;
            unset($sessionData['form_key'], $sessionData['key']);
            $sessionData['password'] = isset($data['password']) && $data['password'] !== ''
                ? $this->encryptor->encrypt($data['password'])
                : '';
            /** @var Session */
            $this->_getSession()->setData('mageplaza_blog_import_data', $sessionData);
            $result = ['import_name' => $importName, 'status' => 'ok'];

            mysqli_close($connect);

            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        } catch (RuntimeException $e) {
            $result = ['import_name' => $data["import_name"], 'status' => 'false'];

            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        } catch (Exception $e) {
            $result = ['import_name' => $data["import_name"], 'status' => 'false'];

            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        }
    }
}
