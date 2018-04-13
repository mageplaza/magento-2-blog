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

/**
 * Class Import
 * @package Mageplaza\Blog\Controller\Adminhtml\Import
 */
class Import extends Action
{
    public function execute()
    {

        $resultRedirect = $this->resultRedirectFactory->create();
        die("Here!");
        $data = $this->getRequest()->getPost('import');

        try {
            mysqli_connect($data["db_host"],$data["user_name"],$data["db_password"],$data["db_name"]);
            $this->messageManager->addSuccess(__('You connected to %1 successfully',$data["import_name"]));
            $resultRedirect->setPath('mageplaza_blog/*/edit');
            $this->_getSession()->setData('mageplaza_blog_import_data', $data);

            return $resultRedirect;


        } catch (LocalizedException $e) {
            echo ($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('False connection to %1',$data["import_name"]));
            $this->_getSession()->setData('mageplaza_blog_import_data', $data);
        }

        $resultRedirect->setPath('mageplaza_blog/*/edit');

        return $resultRedirect;

    }
}
