<?php
/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 * @category  Mageplaza
 * @package   Mageplaza_Blog
 * @copyright Copyright (c) 2016
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 */
namespace Mageplaza\Blog\Model\Post\Source;

class MetaRobots implements \Magento\Framework\Option\ArrayInterface
{
    const INDEXFOLLOW = 1;
    const NOINDEXNOFOLLOW = 2;
    const NOINDEXFOLLOW = 3;
    const INDEXNOFOLLOW = 4;


    /**
     * to option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => self::INDEXFOLLOW,
                'label' => __('INDEX,FOLLOW')
            ],
            [
                'value' => self::NOINDEXNOFOLLOW,
                'label' => __('NOINDEX,NOFOLLOW')
            ],
            [
                'value' => self::NOINDEXFOLLOW,
                'label' => __('NOINDEX,FOLLOW')
            ],
            [
                'value' => self::INDEXNOFOLLOW,
                'label' => __('INDEX,NOFOLLOW')
            ],
        ];

        return $options;
    }

    public function getOptionArray()
    {
        return [self::INDEXFOLLOW => 'INDEX,FOLLOW', self::NOINDEXNOFOLLOW => 'NOINDEX,NOFOLLOW', self::NOINDEXFOLLOW => 'NOINDEX,FOLLOW', self::INDEXNOFOLLOW => 'INDEX,NOFOLLOW'];
    }
}
