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
namespace Mageplaza\Blog\Block\Sidebar;


use Mageplaza\Blog\Block\Frontend;

class Recentpost extends Frontend
{
	public function getRecentPost()
	{
		$ob       = $this->objectManager->get('Mageplaza\Blog\Model\Post');
		$posts = $ob->getCollection();
		$posts->setOrder('created_at', 'DESC');

		return $posts;
	}
	public function getLimitPost()
	{
		return $this->helperData->getBlogConfig('sidebar/number_recent_posts');
	}
}
