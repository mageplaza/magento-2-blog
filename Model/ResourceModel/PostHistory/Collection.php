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

namespace Mageplaza\Blog\Model\ResourceModel\PostHistory;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mageplaza\Blog\Model\PostHistory;
use Psr\Log\LoggerInterface;

/**
 * Class Collection
 * @package Mageplaza\Blog\Model\ResourceModel\PostLike
 */
class Collection extends AbstractCollection
{
    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var string
     */
    private $locale;

    /**
     * Collection constructor.
     *
     * @param TimezoneInterface $localeDate
     * @param ResolverInterface $localeResolver
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        TimezoneInterface $localeDate,
        ResolverInterface $localeResolver,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->localeDate = $localeDate;
        $this->locale = $localeResolver->getLocale();

        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(PostHistory::class, \Mageplaza\Blog\Model\ResourceModel\PostHistory::class);
    }

    /**
     * @return Collection
     * @throws Exception
     */
    protected function _afterLoad()
    {
        foreach ($this->getItems() as $item) {
            $convertedDate = $this->localeDate->date(
                new DateTime($item->getData('updated_at'), new DateTimeZone('UTC')),
                $this->locale,
                true
            );
            $item->setData('updated_at', $convertedDate->format('M j, Y h:i:s A'));
        }

        return parent::_afterLoad();
    }
}
