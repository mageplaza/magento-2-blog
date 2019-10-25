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
class SignupForm extends Frontend
{

    public function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    /**
     * @return mixed
     */
    public function getUrlSuffix()
    {
        return $this->helperData->getUrlSuffix();
    }

    public function getAuthor()
    {
        $author = $this->helperData->getCurrentAuthor();

        if ($author) {
            return [
                'name'              => $author->getName(),
                'url_key'           => $author->getUrlKey(),
                'short_description' => $author->getShortDescription(),
                'image'             => $author->getImage(),
                'facebook_link'     => $author->getFacebookLink(),
                'twitter_link'      => $author->getTwitterLink(),
            ];
        }

        return [
            'name'              => '',
            'url_key'           => '',
            'short_description' => '',
            'image'             => '',
            'facebook_link'     => '',
            'twitter_link'      => '',
        ];
    }
}
