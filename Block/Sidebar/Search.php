<?php
namespace Mageplaza\Blog\Block\Sidebar;

use Mageplaza\Blog\Block\Frontend;

class Search extends Frontend
{
	/**
	 * get search blog's data
	 */
	public function getSearchBlogData()
	{
		$result = [];
		$posts = $this->helperData->getPostList();
		$limitDesc = $this->getSidebarConfig('search/description') ?: 100;

		foreach ($posts as $item) {
			$tmp = array(
				'value' => $item->getName(),
				'url'	=> $this->getUrlByPost($item),
				'image'	=> $item->getImage() ? $this->getImageUrl($item->getImage()) : $this->getDefaultImageUrl(),
				'desc'	=> $item->getShortDescription() ? substr($item->getShortDescription(),0, $limitDesc)
					: 'No description'
			);
			array_push($result, $tmp);
		}

		return json_encode($result);
	}
}