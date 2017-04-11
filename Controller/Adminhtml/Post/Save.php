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
namespace Mageplaza\Blog\Controller\Adminhtml\Post;

class Save extends \Mageplaza\Blog\Controller\Adminhtml\Post
{
    /**
     * Backend session
     *
     * @var \Magento\Backend\Model\Session
     */
	public $backendSession;

    /**
     * Upload model
     *
     * @var \Mageplaza\Blog\Model\Upload
     */
	public $uploadModel;

    /**
     * Image model
     *
     * @var \Mageplaza\Blog\Model\Post\Image
     */
	public $imageModel;

    /**
     * JS helper
     *
     * @var \Magento\Backend\Helper\Js
     */
	public $jsHelper;
	public $trafficFactory;
	protected $authSession;

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
        \Magento\Backend\Helper\Js $jsHelper,
        \Mageplaza\Blog\Model\PostFactory $postFactory,
        \Magento\Framework\Registry $registry,
		\Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Backend\App\Action\Context $context
    ) {
    
        $this->uploadModel    = $uploadModel;
        $this->imageModel     = $imageModel;
        $this->trafficFactory = $trafficFactory;
        $this->backendSession = $context->getSession();
        $this->jsHelper       = $jsHelper;
		$this->authSession = $authSession;
        parent::__construct($postFactory, $registry, $context);
    }

    /**
     * run the action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
		$user = $this->authSession->getUser();
        $data = $this->getRequest()->getPost('post');
        //var_dump($data);die();
        $data['store_ids'] = implode(',', $data['store_ids']);
		$data['modifier_id'] = $user->getId();
        //check delete image
		$deleteImage = false;
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $post = $this->initPost();
            $post->setData($data);
			if (isset($data['image'])) {
				if (isset($data['image']['delete']) && $data['image']['delete'] == '1') {
					unset($data['image']);
					$post->setImage('');
					$deleteImage = true;
				}
			}

            if ((!isset($data['image']) || (count($data['image']) == 1)) && !$deleteImage) {
				$image = $this->uploadModel->uploadFileAndGetName('image', $this->imageModel->getBaseDir(), $data);
				if ($image === false) {
					$this->messageManager->addError(__('Please choose an image to upload.'));
					$resultRedirect->setPath(
						'mageplaza_blog/*/edit',
						[
							'post_id'  => $post->getId(),
							'_current' => true
						]
					);

					return $resultRedirect;
				}

				$post->setImage($image);
			}

            $tags = $this->getRequest()->getPost('tags', -1);
            if ($tags != -1) {
                $post->setTagsData($this->jsHelper->decodeGridSerializedInput($tags));
            }
            $topics = $this->getRequest()->getPost('topics', -1);
            if ($topics != -1) {
                $post->setTopicsData($this->jsHelper->decodeGridSerializedInput($topics));
            }

//            $categoryIds = $this->getRequest()->getPost('categories_ids',-1);
//



            if (!isset($data['categories_ids'])) {
                $post->setCategoriesIds([]);
            }
//            else{
//
//
//                $post->setCategoriesIds($this->jsHelper->decodeGridSerializedInput($categoryIds));
//            }
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
}