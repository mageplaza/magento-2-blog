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
 * @copyright   Copyright (c) 2016 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */
namespace Mageplaza\Blog\Model\Config\Source\Comments;

class Type implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    const DISQUS = 3;
    const FACEBOOK = 2;
    const DEFAULT_COMMENT = 1;
    const DISABLE = 4;

    public function toOptionArray()
    {
        return [
        	['value' => self::DEFAULT_COMMENT, 'label' => __('Default Comment')],
        	['value' => self::DISQUS, 'label' => __('Disqus Comment')],
			['value' => self::FACEBOOK, 'label' => __('Facebook Comment')],
			['value' => self::DISABLE, 'label' => __('Disable Completely')]
		];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
        	self::DISABLE => __('Disable Completely'),
			self::DEFAULT_COMMENT => __('Default Comment'),
			self::FACEBOOK => __('Facebook Comment'),
			self::DISQUS=> __('Disqus Comment')
		];
    }
}
