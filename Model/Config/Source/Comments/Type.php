<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection
 *
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
        return [ ['value' => self::DISQUS, 'label' => __('Disqus Comment')], ['value' => self::FACEBOOK, 'label' => __('Facebook Comment')], ['value' => self::DISABLE, 'label' => __('Disable Completely')]];
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [self::DISABLE => __('Disable Completely'), self::FACEBOOK => __('Facebook Comment'),self::DISQUS=> __('Disqus Comment')];
    }
}
