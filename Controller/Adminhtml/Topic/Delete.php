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
namespace Mageplaza\Blog\Controller\Adminhtml\Topic;

class Delete extends \Mageplaza\Blog\Controller\Adminhtml\Topic
{
    /**
     * execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('topic_id');
        if ($id) {
            $name = "";
            try {
                /** @var \Mageplaza\Blog\Model\Topic $topic */
                $topic = $this->topicFactory->create();
                $topic->load($id);
                $name = $topic->getName();
                $topic->delete();
                $this->messageManager->addSuccess(__('The Topic has been deleted.'));
                $this->_eventManager->dispatch(
                    'adminhtml_mageplaza_blog_topic_on_delete',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('mageplaza_blog/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_mageplaza_blog_topic_on_delete',
                    ['name' => $name, 'status' => 'fail']
                );
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                $resultRedirect->setPath('mageplaza_blog/*/edit', ['topic_id' => $id]);
                return $resultRedirect;
            }
        }
        // display error message
        $this->messageManager->addError(__('Topic to delete was not found.'));
        // go to grid
        $resultRedirect->setPath('mageplaza_blog/*/');
        return $resultRedirect;
    }
}
