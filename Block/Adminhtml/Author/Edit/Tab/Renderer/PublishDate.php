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

namespace Mageplaza\Blog\Block\Adminhtml\Author\Edit\Tab\Renderer;

use DateTime;
use DateTimeZone;
use Exception;
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class PublishDate
 * @package Mageplaza\Blog\Block\Adminhtml\Author\Edit\Tab\Renderer
 */
class PublishDate extends Text
{
    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * PublishDate constructor.
     *
     * @param TimezoneInterface $timezone
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        TimezoneInterface $timezone,
        Context $context,
        array $data = []
    ) {
        $this->timezone = $timezone;

        parent::__construct($context, $data);
    }

    /**
     * @param DataObject $row
     *
     * @return string
     * @throws Exception
     */
    public function render(DataObject $row)
    {
        $date = $this->convertTimeZone($row->getData($this->getColumn()->getIndex()));
        $row->setData($this->getColumn()->getIndex(), $date->format('Y-m-d h:i:s A'));

        return parent::render($row);
    }

    /**
     * @param string $date
     *
     * @return DateTime
     * @throws Exception
     */
    public function convertTimeZone($date)
    {
        $dateTime = new DateTime($date, new DateTimeZone('UTC'));
        $dateTime->setTimezone(new DateTimeZone($this->timezone->getConfigTimezone()));

        return $dateTime;
    }
}
