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

define([
        'jquery'
    ], function ($) {
    "use strict";

        var parentCategory = $(".mp-blog-expand-tree-2");
        var childCategory  = $(".mp-blog-expand-tree-3");

        parentCategory.click(function () {
            if ($(this).hasClass("mp-blog-expand-tree-2")) {
                $(this).parent().find(".category-level3").slideDown("fast");
                $(this).removeClass("mp-blog-expand-tree-2 fa fa-plus-square-o")
                .addClass("mp-blog-narrow-tree-2 fa fa-minus-square-o");
            } else {
                $(this).parent().find(".category-level4").slideUp("fast");
                $(this).parent().find(".category-level3").slideUp("fast");
                $(this).removeClass("mp-blog-narrow-tree-2 fa fa-minus-square-o")
                .addClass("mp-blog-expand-tree-2 fa fa-plus-square-o");
                $(this).parent().find(".mp-blog-narrow-tree-3")
                .removeClass("mp-blog-narrow-tree-3 fa fa-minus-square-o")
                .addClass("mp-blog-expand-tree-3 fa fa-plus-square-o");
            }

        });

        childCategory.click(function () {
            if ($(this).hasClass("mp-blog-expand-tree-3")) {
                $(this).parent().find(".category-level4").slideDown("fast");
                $(this).removeClass("mp-blog-expand-tree-3 fa fa-plus-square-o")
                .addClass("mp-blog-narrow-tree-3 fa fa-minus-square-o");
            } else {
                $(this).parent().find(".category-level4").slideUp("fast");
                $(this).removeClass("mp-blog-narrow-tree-3 fa fa-minus-square-o")
                .addClass("mp-blog-expand-tree-3 fa fa-plus-square-o");
            }
        });
    }
);