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
    'use strict';

    $.widget('mageplaza.mpBlogHelpfulRate', {
            options: {
                url: '',
                post_id: '',
                mode: ''
            },
            _create: function () {
                var post_id = this.options.post_id,
                    url     = this.options.url,
                    self    = this;

                $('#mp-blog-review div').each(function () {
                    var el = this;

                    if (self.options.mode === 0 && self.getCookie('mpblog_post_data')){
                        self.disableReview();
                    }

                    if (self.options.mode === 1){
                        $.ajax({
                            url: url,
                            type: "post",
                            data: {
                                post_id: post_id,
                                action: '3',
                                mode: self.options.mode
                            },
                            showLoader: false,
                            success: function (response) {
                                if (response.status === 0){
                                    self.disableReview();
                                }
                            }
                        });
                    }

                    $(el).on('click', function () {
                        var action         = 0,
                            currentPostIds = {};

                        if (JSON.parse(self.getCookie('mpblog_post_data'))) {
                            currentPostIds = JSON.parse(self.getCookie('mpblog_post_data'));
                        }

                        if ($(this).hasClass('mp-blog-like')) {
                            action = 1;
                        }

                        if (typeof currentPostIds[post_id] !== "undefined" && self.options.mode === 0) {
                            $('.mp-blog-review-label').html('<div class="message message-error error">' +
                                '<div data-ui-id="magento-framework-view-element-messages-2-message-error">' +
                                'You have voted already!' +
                                '</div></div>');
                        } else {
                            $.ajax({
                                url: url,
                                type: "post",
                                data: {
                                    post_id: post_id,
                                    action: action,
                                    mode: self.options.mode
                                },
                                showLoader: true,
                                success: function (response) {
                                    var storedPostIds = self.receiveCookiePostIds(post_id, action, self),
                                        jsonStringIds    = JSON.stringify(storedPostIds);

                                    if (self.options.mode === 0){
                                        document.cookie = 'mpblog_post_data = ' + jsonStringIds;
                                    }

                                    if (response['status'] && response['type'] === '1') {
                                        $('#mp-blog-review .mp-blog-like .mp-blog-view')
                                        .text('(' + response["sum"] + ')');
                                    }
                                    if (response['status'] && response['type'] === '0') {
                                        $('#mp-blog-review .mp-blog-dislike .mp-blog-view')
                                        .text('(' + response["sum"] + ')');
                                    }
                                    $('html, body').animate({
                                        scrollTop: $('body').offset().top
                                    }, 500);
                                }
                            });
                        }
                    });
                });
            },
            disableReview: function () {
                $('#mp-blog-review').css('pointer-events','none');
                $('.mp-blog-like').css('background-color','#658259');
                $('.mp-blog-dislike').css('background-color','##9a6464');
            }
            ,
            getCookie: function (name) {
                var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');

                return v ? v[2] : null;
            },
            receiveCookiePostIds: function (postId, action, self) {
                var postData        = {
                        id: postId,
                        type: action
                    },
                    receivedJsonStr = self.getCookie('mpblog_post_data'),
                    postIds         = JSON.parse(receivedJsonStr);

                if (postIds == null) {
                    postIds = {};
                }
                if (typeof postIds[postId] !== "undefined") {

                    return postIds;
                }
                postIds[postId] = postData;

                return postIds;
            }
        }
    );

    return $.mageplaza.mpBlogHelpfulRate;
});
