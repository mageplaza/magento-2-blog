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

namespace Mageplaza\Blog\Controller\Post;

use Exception;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\Blog\Helper\Data;
use Mageplaza\Blog\Helper\Image;
use Mageplaza\Blog\Model\PostFactory;
use Mageplaza\Blog\Model\ResourceModel\Author\Collection as AuthorCollection;

/**
 * Class Manage
 * @package Mageplaza\Blog\Controller\Post
 */
class Manage extends Action
{
    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var Data
     */
    protected $_helperBlog;

    /**
     * @var AuthorCollection
     */
    protected $authorCollection;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @var PostFactory
     */
    protected $postFactory;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * View constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param PostFactory $postFactory
     * @param AuthorCollection $authorCollection
     * @param Session $customerSession
     * @param Registry $coreRegistry
     * @param DateTime $date
     * @param Image $imageHelper
     * @param Data $helperData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        PostFactory $postFactory,
        AuthorCollection $authorCollection,
        Session $customerSession,
        Registry $coreRegistry,
        DateTime $date,
        Image $imageHelper,
        Data $helperData
    ) {
        $this->_helperBlog = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->authorCollection = $authorCollection;
        $this->customerSession = $customerSession;
        $this->coreRegistry = $coreRegistry;
        $this->postFactory = $postFactory;
        $this->date = $date;
        $this->imageHelper = $imageHelper;

        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|ResultInterface|null
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $this->_helperBlog->setCustomerContextId();
        $author = $this->_helperBlog->getCurrentAuthor();
        $post = $this->postFactory->create();

        if (!$author) {
            return null;
        }

        if ($this->getRequest()->getFiles('image')['size'] > 0) {
            try {
                $this->imageHelper->uploadImage($data, 'image', Image::TEMPLATE_MEDIA_TYPE_POST, $post->getImage());
            } catch (Exception $exception) {
                $data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
            }
        }

        if (isset($data['image']['delete']) || (isset($data['image']) && $data['image'] === 'null')) {
            $data['image'] = '';
        }

        $data['categories_ids'] = (isset($data['categories_ids']) && $data['categories_ids']) ? explode(
            ',',
            $data['categories_ids'] ?? ''
        ) : [];
        $data['tags_ids'] = (isset($data['tags_ids']) && $data['tags_ids'])
            ? explode(',', $data['tags_ids'] ?? '') : [];
        $data['topics_ids'] = (isset($data['topics_ids']) && $data['topics_ids']) ? explode(
            ',',
            $data['topics_ids'] ?? ''
        ) : [];

        $data['author_id'] = $author->getId();

        $data['store_ids'] = $this->_helperBlog->getCurrentStoreId();

        $data['enabled'] = $this->_helperBlog->getConfigGeneral('auto_post') ? 1 : 0;

        $data['in_rss'] = '0';

        $data['meta_robots'] = 'INDEX,FOLLOW';

        $data['layout'] = 'empty';

        $data['publish_date'] = !empty($data['publish_date']) ? $data['publish_date'] : $this->date->date();

        if ($data['post_id']) {
            $post->load($data['post_id']);
            if ($post->getId()) {
                $post->setData($data);
            }
            $data['updated_at'] = $this->date->date();
        } else {
            unset($data['post_id']);
            $data['created_at'] = $this->date->date();
            $post->setData($data);
        }

        try {
            $post->save();
            $this->messageManager->addSuccessMessage(__('The post has been saved.'));

            return $this->getResponse()->representJson(Data::jsonEncode([
                'status' => 1
            ]));
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());

            return $this->getResponse()->representJson(Data::jsonEncode([
                'status' => 0
            ]));
        }
    }
}
