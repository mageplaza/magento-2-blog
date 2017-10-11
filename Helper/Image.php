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
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Blog\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;
use Psr\Log\LoggerInterface;

/**
 * Class Template
 * @package Mageplaza\GiftCard\Helper
 */
class Image extends AbstractData
{
    const TEMPLATE_MEDIA_PATH = 'mageplaza/blog';
    const TEMPLATE_MEDIA_TYPE_AUTH = 'auth';
    const TEMPLATE_MEDIA_TYPE_POST = 'post';

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     */
    protected $mediaDirectory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $imageFactory;

    /**
     * Image constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        AdapterFactory $imageFactory
    )
    {
        parent::__construct($context, $objectManager, $storeManager);

        $this->mediaDirectory  = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->uploaderFactory = $uploaderFactory;
        $this->imageFactory    = $imageFactory;
    }

    /**
     * @param $data
     * @param string $fileName
     * @param string $type
     * @param null $oldImage
     * @return $this
     */
    public function uploadImage(&$data, $fileName = 'image', $type = '', $oldImage = null)
    {
        if (isset($data[$fileName]) && isset($data[$fileName]['delete']) && $data[$fileName]['delete']) {
            if ($oldImage) {
                $this->removeImage($oldImage);
            }
            $data['image'] = '';
        } else {
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => $fileName]);
                $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(true);
                $uploader->setAllowCreateFolders(true);

                $path = $this->getBaseMediaPath($type);

                $image = $uploader->save(
                    $this->mediaDirectory->getAbsolutePath($path)
                );

                if ($oldImage) {
                    $this->removeImage($oldImage);
                }

                $data['image'] = $path . '/' . $this->_prepareFile($image['file']);
            } catch (\Exception $e) {
                $data['image'] = isset($data['image']['value']) ? $data['image']['value'] : '';
            }
        }

        return $this;
    }

    /**
     * Resize Image Function
     * @param $image
     * @param null $width
     * @param null $height
     * @return string
     */
    public function resizeImage($image, $width = null, $height = null)
    {
        /** @var \Magento\Framework\Filesystem\Directory\WriteInterface $mediaDirectory */
        $mediaDirectory = $this->getMediaDirectory();
        if ($width) {
            $height = $height ?: $width;

            $imageFile = $this->getMediaPath(
                $this->getExcludeMediaPath($image, Image::TEMPLATE_MEDIA_TYPE_POST),
                Image::TEMPLATE_MEDIA_TYPE_POST . '/resize/' . $width . 'x' . $height
            );
            if (!$mediaDirectory->isFile($imageFile)) {
                try {
                    $imageResize = $this->imageFactory->create();
                    $imageResize->open($mediaDirectory->getAbsolutePath($image));
                    $imageResize->constrainOnly(true);
                    $imageResize->keepTransparency(true);
                    $imageResize->keepFrame(false);
                    $imageResize->keepAspectRatio(true);
                    $imageResize->resize($width, $height);
                    $imageResize->save($mediaDirectory->getAbsolutePath($imageFile));

                    $image = $imageFile;
                } catch (\Exception $e) {
                    $this->objectManager->get(LoggerInterface::class)->critical($e->getMessage());
                }
            } else {
                $image = $imageFile;
            }
        }

        return $this->getMediaUrl($image);
    }

    /**
     * @param $imageFile
     * @return $this
     */
    public function removeImage($imageFile)
    {
        $file = $this->mediaDirectory->getRelativePath($imageFile);
        if ($this->mediaDirectory->isFile($file)) {
            $this->mediaDirectory->delete($imageFile);
        }

        return $this;
    }

    /**
     * @param $pathFile
     * @param string $type
     * @return string
     */
    public function getExcludeMediaPath($pathFile, $type = '')
    {
        $pathFile = $this->_prepareFile($pathFile);
        $basePath = $this->getBaseMediaPath($type);
        $pos      = strpos($pathFile, $basePath);
        if ($pos == 0) {
            $pathFile = substr($pathFile, strlen($basePath));
        }

        return trim($pathFile, '/');
    }

    /**
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    public function getMediaDirectory()
    {
        return $this->mediaDirectory;
    }

    /**
     * @param string $type
     * @return string
     */
    public function getBaseMediaPath($type = '')
    {
        return trim(self::TEMPLATE_MEDIA_PATH . '/' . $type, '/');
    }

    /**
     * @param $file
     * @param string $type
     * @return string
     */
    public function getMediaPath($file, $type = '')
    {
        return $this->getBaseMediaPath($type) . '/' . $this->_prepareFile($file);
    }

    /**
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return rtrim($this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA), '/');
    }

    /**
     * @param string $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        return $this->getBaseMediaUrl() . '/' . $this->_prepareFile($file);
    }

    /**
     * @param string $file
     * @return string
     */
    protected function _prepareFile($file)
    {
        return ltrim(str_replace('\\', '/', $file), '/');
    }
}
