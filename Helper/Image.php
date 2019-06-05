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
	
	namespace Mageplaza\Blog\Helper;
	
	use Mageplaza\Core\Helper\Media;
	
	/**
	 * Class Image
	 * @package Mageplaza\Blog\Helper
	 */
	class Image extends Media
	{
		const TEMPLATE_MEDIA_PATH = 'mageplaza/blog';
		const TEMPLATE_MEDIA_TYPE_AUTH = 'auth';
		const TEMPLATE_MEDIA_TYPE_POST = 'post';
		
		public function uploadImage(&$data, $fileName = 'image', $type = '', $oldImage = null)
		{
			if (isset($data[$fileName]['delete']) && $data[$fileName]['delete']) {
				if ($oldImage) {
					try {
						$this->removeImage($oldImage, $type);
					} catch (\Exception $exception) {
						$this->_logger->critical($exception->getMessage());
					}
				}
				
				$data['image'] = '';
			} else {
				try {
					$uploader = $this->uploaderFactory->create([ 'fileId' => $fileName ]);
					$uploader->setAllowedExtensions([ 'jpg', 'jpeg', 'gif', 'png' ]);
					$uploader->setAllowRenameFiles(true);
					$uploader->setFilesDispersion(true);
					$uploader->setAllowCreateFolders(true);
					
					$path = $this->getBaseMediaPath($type);
					
					$image = $uploader->save($this->mediaDirectory->getAbsolutePath($path));
					
					if ($oldImage) {
						$this->removeImage($oldImage, $type);
					}
					
					$data['image'] = $this->_prepareFile($image['file']);
				} catch (\Exception $exception) {
					$data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
				}
			}
			
			return $this;
		}
	}
