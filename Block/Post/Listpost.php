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

namespace Mageplaza\Blog\Block\Post;

use Mageplaza\Blog\Block\Frontend;

/**
 * Class Listpost
 * @package Mageplaza\Blog\Block\Post
 */
class Listpost extends Frontend
{

	/**
	 * @return string
	 */
	public function checkRss()
	{
		return $this->helperData->getBlogUrl('post/rss');
	}

	/**
	 * @param $page
	 * @return string
	 */
	public function getPageLink($page)
	{

		if ($this->getMonthParam()) {
			$pageLink = $this->getUrl('*/*/*', ['_use_rewrite' => true]) . '?month=' . $this->getMonthParam() . '&p=' . $page;
		} else {
			$pageLink = $this->getUrl('*/*/*', ['_use_rewrite' => true]) . '?p=' . $page;
		}

		return $pageLink;
	}

	/**
	 * @param $currentPage
	 * @return string
	 */
	public function getPrevPage($currentPage)
	{
		$html = '';

		if ($currentPage > 1) {
			$html = '<li class="item mp-page-item">
							<a href="' . $this->getPageLink($currentPage - 1) . '" class="page">
							<span class="label">' . __('Page') . '</span>
								<span><</span>
						</a>
					</li>';
		}

		return $html;
	}

	/**
	 * @param $currentPage
	 * @return string
	 */
	public function getFirstPage($currentPage)
	{
		$html  = '';
		$start = 1;

		if ($currentPage > ($start + 2)) {

			$html = '<li class="item mp-page-item">
                    <a href="' . $this->getPageLink($start) . '" class="page">
                    <span class="label">' . __('Page') . '</span>
                        <span>' . $start . '</span>
                </a>
            </li>';
			if ($currentPage > ($start + 3)) {
				$html .= '<li class="item mp-page-item">
                        <span>...</span>
            </li>';
			}

		}

		return $html;
	}

	/**
	 * @param $currentPage
	 * @param $end
	 * @return string
	 */
	public function getLastPage($currentPage, $end)
	{
		$html = '';

		if ($currentPage < ($end - 2)) {

			if ($currentPage < ($end - 3)) {
				$html = '<li class="item mp-page-item">
                    <span>...</span>
            			</li>';
			}
			$html .= '<li class="item mp-page-item">
                <a href="' . $this->getPageLink($end) . '" class="page">
                <span class="label">' . __('Page') . '</span>
                    <span>' . $end . '</span>
            </a>
            </li>';

		}

		return $html;
	}

	/**
	 * @param $currentPage
	 * @param $countPage
	 * @return string
	 */
	public function getNextPage($currentPage, $countPage)
	{
		$html = '';


		if ($currentPage < $countPage) {

			$html = '<li class="item mp-page-item">
                    <a href="' . $this->getPageLink($currentPage + 1) . '" class="page">
                    <span class="label">' . __('Page') . '</span>
                        <span>></span>
                </a>
            </li>';


		}

		return $html;
	}

	/**
	 * @param $currentPage
	 * @param $relatedPage
	 * @param $i
	 * @return string
	 */
	public function getAllPage($currentPage, $relatedPage, $i)
	{
		$start = 1;


		if ($currentPage == $start) {
			$html = '<li class="item mp-page-item">';

			if ($currentPage == $i) {
				$html .= '<span class="selected_page">' . $i . '</span>';

			} else {

				$html .= '<a href="' . $this->getPageLink($i) . '" class="page">
                        <span class="label">' . __('Page') . '</span>
                            <span>' . $i . '</span>
                    </a>';

			}
			$html .= '</li>';

		} else {

			$html = '<li class="item mp-page-item">';

			if ($currentPage == $relatedPage) {

				$html .= '<span class="selected_page">' . $relatedPage . '</span>';

			} else {

				$html .= '<a href="' . $this->getPageLink($relatedPage) . '" class="page">
                            <span class="label">' . __('Page') . '</span>
                                <span>' . $relatedPage . '</span>
                        </a>
						</li>';

			}
		}

		return $html;
	}
}
