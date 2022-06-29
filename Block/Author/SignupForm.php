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
    /**
     * @return mixed
     */
    public function getUrlSuffix()
    {
        return $this->helperData->getUrlSuffix();
    }

    /**
     * @return array
     */
    public function getAuthor()
    {
        $author = $this->coreRegistry->registry('mp_author');

        if ($author) {
            return [
                'name' => $author->getName(),
                'status' => $this->authorStatusType->toArray()[$author->getStatus()],
                'url_key' => $author->getUrlKey(),
                'short_description' => $author->getShortDescription(),
                'image' => $author->getImage(),
                'facebook_link' => $author->getFacebookLink(),
                'twitter_link' => $author->getTwitterLink(),
            ];
        }

        return [
            'name' => '',
            'url_key' => '',
            'short_description' => '',
            'image' => '',
            'facebook_link' => '',
            'twitter_link' => '',
        ];
    }

    /**
     * @return mixed
     */
    public function getVersion()
    {
        $array = explode('/', $this->helperData->getConfigValue('cms/wysiwyg/editor') ?? '');
        if ($array[count($array) - 1] === 'tinymce4Adapter') {
            return 4;
        }

        return 3;
    }

    /**
     * @return int
     */
    public function getMagentoVersion()
    {
        return (int)$this->helperData->versionCompare('2.3.0') ? 3 : 2;
    }
}
