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

namespace Mageplaza\Blog\Model\Import;

use Magento\Backend\Model\Auth\Session;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;
use Mageplaza\Blog\Helper\Data as HelperData;
use Mageplaza\Blog\Helper\Image as HelperImage;
use Mageplaza\Blog\Model\CategoryFactory;
use Mageplaza\Blog\Model\CommentFactory;
use Mageplaza\Blog\Model\Config\Source\Import\Type;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\TagFactory;
use Mageplaza\Blog\Model\TopicFactory;
use Mageplaza\Blog\Model\AuthorFactory;

/**
 * Class AbstractImport
 * @package Mageplaza\Blog\Model\Import
 */
abstract class AbstractImport extends AbstractModel
{
    /**
     * @var DateTime
     */
    public $date;

    /**
     * @var Type
     */
    public $importType;

    /**
     * @var HelperData
     */
    public $helperData;

    /**
     * @var PostFactory
     */
    protected $_postFactory;

    /**
     * @var TagFactory
     */
    protected $_tagFactory;

    /**
     * @var CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var TopicFactory
     */
    protected $_topicFactory;

    /**
     * @var CommentFactory
     */
    protected $_commentFactory;

    /**
     * @var UserFactory
     */
    protected $_userFactory;

    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var HelperImage
     */
    protected $_helperImage;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ResourceConnection
     */
    protected $_resourceConnection;

    /**
     * @var Session
     */
    protected $_authSession;

    /**
     * Error Count Statistic
     * @var int
     */
    protected $_errorCount = 0;

    /**
     * Success Count Statistic
     * @var int
     */
    protected $_successCount = 0;

    /**
     * @var bool
     */
    protected $_hasData = false;

    /**
     * @var array
     */
    protected $_type;

    /**
     * @var AuthorFactory
     */
    protected $authorFactory;

    /**
     * AbstractImport constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param PostFactory $postFactory
     * @param TagFactory $tagFactory
     * @param CategoryFactory $categoryFactory
     * @param TopicFactory $topicFactory
     * @param CommentFactory $commentFactory
     * @param AuthorFactory $authorFactory
     * @param UserFactory $userFactory
     * @param CustomerFactory $customerFactory
     * @param ObjectManagerInterface $objectManager
     * @param Session $authSession
     * @param ResourceConnection $resourceConnection
     * @param DateTime $date
     * @param Type $importType
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManager
     * @param HelperImage $helperImage
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PostFactory $postFactory,
        TagFactory $tagFactory,
        CategoryFactory $categoryFactory,
        TopicFactory $topicFactory,
        CommentFactory $commentFactory,
        AuthorFactory $authorFactory,
        UserFactory $userFactory,
        CustomerFactory $customerFactory,
        ObjectManagerInterface $objectManager,
        Session $authSession,
        ResourceConnection $resourceConnection,
        DateTime $date,
        Type $importType,
        HelperData $helperData,
        StoreManagerInterface $storeManager,
        HelperImage $helperImage,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->date                = $date;
        $this->importType          = $importType;
        $this->_type               = $this->_getImportType();
        $this->helperData          = $helperData;
        $this->_postFactory        = $postFactory;
        $this->_tagFactory         = $tagFactory;
        $this->_categoryFactory    = $categoryFactory;
        $this->_topicFactory       = $topicFactory;
        $this->_commentFactory     = $commentFactory;
        $this->_userFactory        = $userFactory;
        $this->_customerFactory    = $customerFactory;
        $this->_objectManager      = $objectManager;
        $this->_resourceConnection = $resourceConnection;
        $this->_authSession        = $authSession;
        $this->_storeManager       = $storeManager;
        $this->_helperImage        = $helperImage;
        $this->authorFactory       = $authorFactory;

        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Import Post Function
     *
     * @param array $data
     * @param null $connection
     *
     * @return mixed
     */
    abstract protected function _importPosts($data, $connection);

    /**
     * Import Tag Function
     *
     * @param array $data
     * @param null $connection
     *
     * @return mixed
     */
    abstract protected function _importTags($data, $connection);

    /**
     * Import Category Function
     *
     * @param array $data
     * @param null $connection
     *
     * @return mixed
     */
    abstract protected function _importCategories($data, $connection);

    /**
     * Import Comment Function
     *
     * @param array $data
     * @param null $connection
     *
     * @return mixed
     */
    abstract protected function _importComments($data, $connection);

    /**
     * Import Author Function
     *
     * @param array $data
     * @param null $connection
     *
     * @return mixed
     */
    abstract protected function _importAuthors($data, $connection);

    /**
     * Get import statistics
     *
     * @param string $type
     * @param int $successCount
     * @param int $errorCount
     * @param bool $hasData
     *
     * @return array
     */
    protected function _getStatistics($type, $successCount, $errorCount, $hasData)
    {
        $statistics = [
            'type'          => $type,
            'success_count' => $successCount,
            'error_count'   => $errorCount,
            'has_data'      => $hasData
        ];

        return $statistics;
    }

    /**
     * Reset statistic record
     */
    protected function _resetRecords()
    {
        $this->_errorCount   = 0;
        $this->_successCount = 0;
        $this->_hasData      = false;
    }

    /**
     * Auto generate password
     *
     * @param int $length
     * @param bool $add_dashes
     * @param string $available_sets
     *
     * @return bool|string
     */
    protected function _generatePassword($length = 9, $add_dashes = false, $available_sets = 'luds')
    {
        $sets = [];
        if (strpos($available_sets, 'l') !== false) {
            $sets[] = 'abcdefghjkmnpqrstuvwxyz';
        }
        if (strpos($available_sets, 'u') !== false) {
            $sets[] = 'ABCDEFGHJKMNPQRSTUVWXYZ';
        }
        if (strpos($available_sets, 'd') !== false) {
            $sets[] = '23456789';
        }
        if (strpos($available_sets, 's') !== false) {
            $sets[] = '!@#$%&*?';
        }
        $all      = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all      .= $set;
        }
        $all = str_split($all);
        // phpcs:disable Generic.CodeAnalysis
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }
        $password = str_shuffle($password);
        if (!$add_dashes) {
            return $password;
        }
        $dash_len = floor(sqrt($length));
        $dash_str = '';
        while (strlen($password) > $dash_len) {
            $dash_str .= substr($password, 0, $dash_len) . '-';
            $password = substr($password, $dash_len);
        }
        $dash_str .= $password;

        return $dash_str;
    }

    /**
     * Get import types
     *
     * @return array
     */
    protected function _getImportType()
    {
        $types = [];
        foreach ($this->importType->toOptionArray() as $item) {
            $types[$item['value']] = $item['value'];
        }

        return $types;
    }
}
