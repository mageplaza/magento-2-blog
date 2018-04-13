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
use Mageplaza\Blog\Helper\Data as BlogHelper;

/**
 * Class Import
 * @package Mageplaza\Blog\Controller\Adminhtml\Import
 */
class Validate extends Action
{
    /**
     * @var BlogHelper
     */
    public $blogHelper;

    /**
     * Validate constructor.
     * @param Context $context
     * @param BlogHelper $blogHelper
     */
    public function __construct(
       Context $context,
       BlogHelper $blogHelper
    )
    {
        $this->blogHelper = $blogHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
//        $connect = mysqli_connect($data["host"],$data["userName"],$data["passWord"],$data["database"]);
//        if (!$connect){
////            $result = true;
//            return $this->getResponse()->representJson("false");
//        }else{
//            return $this->getResponse()->representJson("true");
//        }
        try {
            mysqli_connect($data["host"],$data["userName"],$data["passWord"],$data["database"]);
            $importName = $data["importName"];
            $result = ['importName' => $importName,'status' => 'ok'];
            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        } catch (LocalizedException $e) {
            $result = ['importName' => $data["importName"],'status' => 'false'];
            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        } catch (\RuntimeException $e) {
            $result = ['importName' => $data["importName"],'status' => 'false'];
            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        } catch (\Exception $e) {
            $result = ['importName' => $data["importName"],'status' => 'false'];
            return $this->getResponse()->representJson(BlogHelper::jsonEncode($result));
        }
    }
}
