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

namespace Mageplaza\Blog\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Class Sitemap
 * @package Mageplaza\Blog\Block
 */
class Sitemap extends Frontend
{
    /**
     * @return $this|void
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($breadcrumbs = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbs->addCrumb('sitemap', [
                'label' => __('Site Map'),
                'title' => __('Site Map')
            ]);
        }
    }

    /**
     * @param bool $meta
     *
     * @return array|Phrase
     */
    public function getBlogTitle($meta = false)
    {
        $blogTitle = parent::getBlogTitle($meta);

        if ($meta) {
            $blogTitle[] = __('Site Map');
        } else {
            $blogTitle = __('Site Map');
        }

        return $blogTitle;
    }
}
