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
namespace Mageplaza\Blog\Block\Category;

use Mageplaza\Blog\Block\Frontend;

/**
 * Class Widget
 * @package Mageplaza\Blog\Block\Category
 */
class Widget extends Frontend
{

	/**
	 * @return array|string
	 */
    public function getCategoryList()
    {

		$tree = $this->objectManager->create('Mageplaza\Blog\Block\Adminhtml\Category\Tree');
		$tree = $tree->getTree();
		$tree = $this->helperData->filterItems($tree);

		return $this->getCategoryTree($tree);
    }

	/**
	 * Generate Category Tree Html
	 * @param $tree
	 */
    public function getCategoryTree($tree){

		if ($tree){
			foreach ($tree as $value){
				$level = count(explode('/',($value['path'])));

				if(isset($value['children']) && $level < 4 ){
					echo '<li class="category-level'.$level.' category-item">
						<i class="fa fa-plus-square-o mp-blog-expand-tree-'.$level.'"></i>		
						<a class="list-categories" href="'.$this->getCategoryUrl($value['url']).'">
						<i class="fa fa-folder-open-o">&nbsp;&nbsp;</i>'
						.ucfirst($value['text']).
						'</a>';
					$this->getCategoryTree($value['children']);
				}else{
					echo '<li class="category-level'.$level.' category-item">
						<a class="list-categories" href="'.$this->getCategoryUrl($value['url']).'">
						<i class="fa fa-folder-open-o">&nbsp;&nbsp;</i>'
						.ucfirst($value['text']).
						'</a>';
				}
				echo '</li>';

			}
		}else{
			 echo __('No Categories.');
		}
	}

	/**
	 * @param $category
	 * @return string
	 */
    public function getCategoryUrl($category)
    {
        return $this->helperData->getCategoryUrl($category);
    }
}
