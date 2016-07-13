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
namespace Mageplaza\Blog\Block\Post;

use Mageplaza\Blog\Block\Frontend;

class View extends Frontend
{
	/**
	 * @param $topic
	 * @return string
	 */
	public function getTopicUrl($topic)
	{
		return $this->helperData->getTopicUrl($topic);
	}

	/**
	 * @param $tag
	 * @return string
	 */
	public function getTagUrl($tag)
	{
		return $this->helperData->getTagUrl($tag);
	}

	/**
	 * @param $category
	 * @return string
	 */
	public function getCategoryUrl($category)
	{
		return $this->helperData->getCategoryUrl($category);
	}

	/**
	 * @return bool|mixed
	 */
	public function checkComment()
	{
		if (!$this->helperData->getBlogConfig('general/enabled'))
			return false;
		$comment = $this->helperData->getBlogConfig('comment/type');

		return $comment;
	}

	/**
	 * @param $code
	 * @return mixed
	 */
	public function helperComment($code)
	{
		return $this->helperData->getBlogConfig('comment/' . $code);
	}
}
