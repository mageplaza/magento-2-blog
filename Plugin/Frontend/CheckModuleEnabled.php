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

namespace Mageplaza\Blog\Plugin\Frontend;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Mageplaza\Blog\Helper\Data;

/**
 * Class CheckModuleEnabled
 * @package Mageplaza\Blog\Plugin\Frontend
 */
class CheckModuleEnabled
{
    /**
     * Front name declared in etc/frontend/routes.xml for both Blog and BlogPro.
     */
    const BLOG_FRONT_NAME = 'mpblog';

    /**
     * @var Data
     */
    private $helperData;

    /**
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * Block dispatching of any blog controller while the module is disabled.
     *
     * @param Action $subject
     * @param callable $proceed
     * @param RequestInterface $request
     *
     * @return mixed
     * @throws NotFoundException
     */
    public function aroundDispatch(Action $subject, callable $proceed, RequestInterface $request)
    {
        if ($request->getModuleName() === self::BLOG_FRONT_NAME && !$this->helperData->isEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }

        return $proceed($request);
    }
}
