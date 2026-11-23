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

declare(strict_types=1);

namespace Mageplaza\Blog\Model\Config\Source;

use Magento\Config\Model\Config\Source\Locale;
use Magento\Framework\Data\OptionSourceInterface;

class AiLanguage implements OptionSourceInterface
{
    /**
     * @var Locale
     */
    private $locale;

    /**
     * @param Locale $locale
     */
    public function __construct(Locale $locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array_merge([['value' => '', 'label' => __('Same language as the content')]], $this->locale->toOptionArray());
    }
}
