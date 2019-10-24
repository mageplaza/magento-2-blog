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

namespace Mageplaza\Blog\Block\Author;

use Mageplaza\Blog\Block\Frontend;

/**
 * Class SignupForm
 * @package Mageplaza\Blog\Block\Author
 */
class SignupForm extends Frontend{

    public function _prepareLayout()
    {

        $this->pageConfig->getTitle()->set(__('Your Page Title'));

        return parent::_prepareLayout();
    }
}


