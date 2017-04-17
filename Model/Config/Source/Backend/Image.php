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

namespace Mageplaza\Blog\Model\Config\Source\Backend;

class Image extends  \Magento\Config\Model\Config\Backend\Image
{
	const UPLOAD_DIR = 'mageplaza/blog/logo';

	/**
	 * Return path to directory for upload file
	 *
	 * @return string
	 * @throw \Magento\Framework\Exception\LocalizedException
	 */
	protected function _getUploadDir()
	{
		return $this->_mediaDirectory->getAbsolutePath($this->_appendScopeInfo(self::UPLOAD_DIR));
	}

	/**
	 * Makes a decision about whether to add info about the scope.
	 *
	 * @return boolean
	 */
	protected function _addWhetherScopeInfo()
	{
		return true;
	}

	/**
	 * Getter for allowed extensions of uploaded files.
	 *
	 * @return string[]
	 */
	protected function _getAllowedExtensions()
	{
		return ['jpg', 'jpeg', 'gif', 'png', 'svg'];
	}
}