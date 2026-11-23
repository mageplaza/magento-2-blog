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

namespace Mageplaza\Blog\Model\Ai;

use Magento\Store\Model\StoreManagerInterface;

class PostStore
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * @param array|string $storeIds
     * @return int
     */
    public function getStoreId($storeIds): int
    {
        if (is_string($storeIds)) {
            $storeIds = explode(',', $storeIds);
        }
        foreach ((array)$storeIds as $id) {
            if ((int)$id !== 0) {
                return (int)$id;
            }
        }

        return (int)$this->storeManager->getDefaultStoreView()->getId();
    }
}
