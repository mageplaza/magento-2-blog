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
                var post_id   = this.options.post_id,
                    url       = this.options.url,
                    self      = this,
                    subPostId = {};

                if (self.options.mode === 1) {
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
                            if (response.status === 0) {
                                self.disableReview(response.action);
                            }
                        }
                    });
                } else if (JSON.parse(self.getCookie('mpblog_post_data'))) {
                    subPostId = JSON.parse(self.getCookie('mpblog_post_data'));
                    if (typeof subPostId[post_id] !== "undefined") {
                        self.disableReview(subPostId[post_id].type);
                    }
                }

                $('#mp-blog-review div').each(function () {
                    var el = this;


                    $(el).on('click', function () {
                        var action         = 0,
                            currentPostIds = {},
                            likeId         = 0;

                        if (JSON.parse(self.getCookie('mpblog_post_data'))) {
                            currentPostIds = JSON.parse(self.getCookie('mpblog_post_data'));
                        }

                        if ($(this).hasClass('mp-blog-like')) {
                            action = 1;
                        }

                        if (typeof currentPostIds[post_id] !== "undefined" && self.options.mode === 0) {
                            likeId = currentPostIds[post_id].likeId;
                            self.enableReview(currentPostIds[post_id].type);
                            if (action === currentPostIds[post_id].type) {
                                delete currentPostIds[post_id];
                                document.cookie = 'mpblog_post_data = ' + JSON.stringify(currentPostIds);
                            } else {
                                currentPostIds[post_id].type = action;
                                self.disableReview(action);
                            }
                        }
                        $.ajax({
                            url: url,
                            type: "post",
                            data: {
                                post_id: post_id,
                                action: action,
                                mode: self.options.mode,
                                likeId: likeId
                            },
                            showLoader: true,
                            success: function (response) {
                                var storedPostIds,
                                    jsonStringIds;

                                if (response['postLike']
                                    && self.options.mode === 0 && typeof currentPostIds[post_id] === "undefined") {
                                    storedPostIds   =
                                        self.receiveCookiePostIds(post_id, action, response['postLike'], self);
                                    jsonStringIds   = JSON.stringify(storedPostIds);
                                    document.cookie = 'mpblog_post_data = ' + jsonStringIds;
                                }

                                if (response['status']) {
                                    if (response["sumLike"]) {
                                        $('#mp-blog-review .mp-blog-like .mp-blog-view')
                                        .text('(' + response["sumLike"] + ')');
                                    } else {
                                        $('#mp-blog-review .mp-blog-like .mp-blog-view')
                                        .text('');
                                    }
                                    if (response["sumDislike"]) {
                                        $('#mp-blog-review .mp-blog-dislike .mp-blog-view')
                                        .text('(' + response["sumDislike"] + ')');
                                    } else {
                                        $('#mp-blog-review .mp-blog-dislike .mp-blog-view')
                                        .text('');
                                    }
                                }

                                if (response['status']) {
                                    self.enableAllReview();
                                    if (response['postLike']) {
                                        self.disableReview(action);
                                    }
                                }

                                $('html, body').animate({
                                    scrollTop: $('body').offset().top
                                }, 500);
                            }
                        });

                    });
                });
            },
            disableReview: function (action) {
                if ('' + action === '1') {
                    $('.mp-blog-like').css('background-color', '#658259');

                } else {
                    $('.mp-blog-dislike').css('background-color', '#9a6464');
                }
            },
            enableReview: function (action) {
                if ('' + action === '1') {
                    $('.mp-blog-like').css('background-color', '#6AA84F');

                } else {
                    $('.mp-blog-dislike').css('background-color', '#EC3A3C');
                }
            },
            enableAllReview: function () {
                $('.mp-blog-like').css('background-color', '#6AA84F');
                $('.mp-blog-dislike').css('background-color', '#EC3A3C');

            },
            getCookie: function (name) {
                var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');

                return v ? v[2] : null;
            },
            receiveCookiePostIds: function (postId, action, likeId, self) {
                var postData        = {
                        id: postId,
                        type: action,
                        likeId: likeId
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
