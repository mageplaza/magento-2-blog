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
namespace Mageplaza\Blog\Controller\Adminhtml\Post;

class Save extends \Mageplaza\Blog\Controller\Adminhtml\Post
{
    const IMAGE_UPLOAD_PATH = 'mageplaza/blog/post/image';

    /**
     * Upload model
     *
     * @var \Mageplaza\Blog\Model\Upload
     */
    protected $uploadModel;

    /**
     * Image model
     *
     * @var \Mageplaza\Blog\Model\Post\Image
     */
    protected $imageModel;

    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
    protected $backendSession;

    /**
     * JS helper
     *
     * @var \Magento\Backend\Helper\Js
     */
    protected $jsHelper;
    protected $trafficFactory;

    /**
     * constructor
     *
     * @param \Mageplaza\Blog\Model\Upload $uploadModel
     * @param \Mageplaza\Blog\Model\Post\Image $imageModel
     * @param \Magento\Backend\Model\Session $backendSession
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param \Mageplaza\Blog\Model\PostFactory $postFactory
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Mageplaza\Blog\Model\Upload $uploadModel,
        \Mageplaza\Blog\Model\Post\Image $imageModel,
        \Mageplaza\Blog\Model\TrafficFactory $trafficFactory,
        \Magento\Backend\Model\Auth\Session $backendSession,
        \Magento\Backend\Helper\Js $jsHelper,
        \Mageplaza\Blog\Model\PostFactory $postFactory,
        \Magento\Framework\Registry $registry,
        //\Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Backend\App\Action\Context $context
    ) {

        $this->uploadModel    = $uploadModel;
        $this->imageModel     = $imageModel;
        $this->trafficFactory = $trafficFactory;
        $this->backendSession = $backendSession;
        $this->jsHelper       = $jsHelper;
        parent::__construct($postFactory, $registry, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $data = $this->getRequest()->getPost('post');

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $post = $this->initPost();
            //$post->setData($data);

            $image = $this->uploadModel->uploadFileAndGetName('image', $this->imageModel->getBaseDir(), $data);
            if(!empty($image) && strpos($image, self::IMAGE_UPLOAD_PATH) === false) {
                //$post->setImage('mageplaza/blog/post/image' . $image);
                //$post->setImage($image);
                $data = array_merge(
                    $data,
                    [
                        'image' => self::IMAGE_UPLOAD_PATH . $image
                    ]
                );
            } else {
                $data = $this->unsetImageData($data);
            }
            $tags = $this->getRequest()->getPost('tags', -1);
            if ($tags != -1) {
                $post->setTagsData($this->jsHelper->decodeGridSerializedInput($tags));
            }
            $topics = $this->getRequest()->getPost('topics', -1);
            if ($topics != -1) {
                $post->setTopicsData($this->jsHelper->decodeGridSerializedInput($topics));
            }
            if (!isset($data['categories_ids'])) {
                $post->setCategoriesIds([]);
            }

            $post->setData($data);

            $this->_eventManager->dispatch(
                'mageplaza_blog_post_prepare_save',
                [
                    'post'    => $post,
                    'request' => $this->getRequest()
                ]
            );
            try {
                $post->save();

                $trafficModel=$this->trafficFactory->create()->load($post->getId(), 'post_id');
                if (!$trafficModel->getId()) {
                    $trafficData=['post_id'=>$post->getId(),'numbers_view'=>'0'];
                    $trafficModel->setData($trafficData);
                    $trafficModel->save();
                }
                $this->messageManager->addSuccess(__('The Post has been saved.'));
                $this->backendSession->setMageplazaBlogPostData(false);
                if ($this->getRequest()->getParam('back')) {
                    $resultRedirect->setPath(
                        'mageplaza_blog/*/edit',
                        [
                            'post_id'  => $post->getId(),
                            '_current' => true
                        ]
                    );

                    return $resultRedirect;
                }
                $resultRedirect->setPath('mageplaza_blog/*/');

                return $resultRedirect;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Post.'));
            }
            $this->_getSession()->setMageplazaBlogPostData($data);
            $resultRedirect->setPath(
                'mageplaza_blog/*/edit',
                [
                    'post_id'  => $post->getId(),
                    '_current' => true
                ]
            );

            return $resultRedirect;
        }
        $resultRedirect->setPath('mageplaza_blog/*/');

        return $resultRedirect;
    }

    /**
     * Workaround to prevent saving this data in category model and it has to be refactored in future
     * @see Magento\Catalog\Controller\Adminhtml\Category\Save;
     *
     * @param array $rawData
     *
     * @return array
     */
    protected function unsetImageData(array $rawData)
    {
        $data = $rawData;
        // @todo It is a workaround to prevent saving this data in category model and it has to be refactored in future
        if (isset($data['image']) && is_array($data['image'])) {
            if (!empty($data['image']['delete'])) {
                $data['image'] = null;
            } else {
                if (isset($data['image'][0]['name']) && isset($data['image'][0]['tmp_name'])) {
                    $data['image'] = $data['image'][0]['name'];
                } else {
                    unset($data['image']);
                }
            }
        }
        return $data;
    }
}
