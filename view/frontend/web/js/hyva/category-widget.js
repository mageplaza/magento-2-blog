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

document.addEventListener('DOMContentLoaded', function () {
    var parentCategory = document.querySelector('.mp-blog-expand-tree-2');
    var childCategory = document.querySelector('.mp-blog-expand-tree-3');

    if (parentCategory) {
        parentCategory.addEventListener('click', function () {
            var level3 = this.parentElement.querySelector('.category-level3');
            if (this.classList.contains('mp-blog-expand-tree-2')) {
                if (level3) {
                    level3.style.display = 'block';
                }
                this.classList.remove('mp-blog-expand-tree-2', 'fa', 'fa-plus-square-o');
                this.classList.add('mp-blog-narrow-tree-2', 'fa', 'fa-minus-square-o');
            } else {
                var level4 = this.parentElement.querySelector('.category-level4');
                if (level4) {
                    level4.style.display = 'none';
                }
                if (level3) {
                    level3.style.display = 'none';
                }
                this.classList.remove('mp-blog-narrow-tree-2', 'fa', 'fa-minus-square-o');
                this.classList.add('mp-blog-expand-tree-2', 'fa', 'fa-plus-square-o');
                if (this.parentElement.querySelector('.mp-blog-narrow-tree-3')) {
                    this.parentElement.querySelector('.mp-blog-narrow-tree-3')
                        .classList.remove('mp-blog-narrow-tree-3', 'fa', 'fa-minus-square-o');
                    this.parentElement.querySelector('.mp-blog-narrow-tree-3')
                        .classList.add('mp-blog-expand-tree-3', 'fa', 'fa-plus-square-o');
                }
            }
        });
    }

    if (childCategory) {
        childCategory.addEventListener('click', function () {
            var level4 = this.parentElement.querySelector('.category-level4');
            if (this.classList.contains('mp-blog-expand-tree-3')) {
                if (level4) {
                    level4.style.display = 'block';
                }
                this.classList.remove('mp-blog-expand-tree-3', 'fa', 'fa-plus-square-o');
                this.classList.add('mp-blog-narrow-tree-3', 'fa', 'fa-minus-square-o');
            } else {
                if (level4) {
                    level4.style.display = 'none';
                }
                this.classList.remove('mp-blog-narrow-tree-3', 'fa', 'fa-minus-square-o');
                this.classList.add('mp-blog-expand-tree-3', 'fa', 'fa-plus-square-o');
            }
        });
    }
});
