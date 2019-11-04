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
    'jquery',
    'mage/translate',
    'underscore'
], function ($, $t, _) {
    'use strict';

    $.widget('mageplaza.mpBlogHelpfulRate', {
            options: {
                url: '',
                post_id: ''
            },
            _create: function () {
                var post_id = this.options.post_id,
                    url     = this.options.url,
                    self    = this;

                $('#mp-blog-review div').each(function () {
                    var el = this;

                    $(el).on('click', function () {
                        var action = 0;
                        if ($(this).hasClass('mp-blog-like')) {
                            action = 1;
                        }
                        $.ajax({
                            url: url,
                            type: "post",
                            data: {
                                post_id: post_id,
                                action: action
                            },
                            showLoader: true,
                            success: function (response) {
                                if (response['status'] && response['type'] === '1'){
                                    $('#mp-blog-review .mp-blog-like .mp-blog-view').text('('+response["sum"]+')');
                                }
                                if (response['status'] && response['type'] === '0'){
                                    $('#mp-blog-review .mp-blog-dislike .mp-blog-view').text('('+response["sum"]+')');
                                }
                                $('html, body').animate({
                                    scrollTop: $('body').offset().top
                                }, 500);
                            }
                        });
                    });
                });
            }
        }
    );

    return $.mageplaza.mpBlogHelpfulRate;
});
