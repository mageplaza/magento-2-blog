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
    'underscore',
    'Magento_Ui/js/modal/modal'
], function ($, $t, _, modal) {
    'use strict';

    $.widget('mageplaza.mpBlogManagePost', {
            options: {
                newUrl: '',
                duplicateUrl: '',
                deleteUrl: '',
                editUrl: ''

            },
            _create: function () {
                var self      = this,
                    htmlPopup = $('#mp-blog-new-post-popup');

                $('.mp-blog-new-post button').on('click', function () {
                    self._AddNewPost(self, htmlPopup);
                });

                $('.mpblog-post-edit').on('click', function () {
                    self._EditPost(this);
                });

                $('.mpblog-post-duplicate').on('click', function () {
                    self._DuplicatePost(this);
                });

                $('.mpblog-post-delete').on('click', function () {
                    self._DeletePost(this);
                });
            },
            _AddNewPost: function (self, htmlPopup) {
                $.ajax({
                    url: self.options.newUrl,
                    type: "post",
                    showLoader: true,
                    success: function (html) {
                        var popupModal,
                            options = {
                                'type': 'popup',
                                'title': $t('Add New Post'),
                                'responsive': true,
                                'innerScroll': true,
                                'buttons': []
                            };

                        htmlPopup.html(html);
                        popupModal = modal(options, htmlPopup);
                        popupModal.openModal();

                        htmlPopup.trigger('contentUpdated');
                    }
                });
            },
            _EditPost: function (self) {
                $('.mpblog-post-edit').on('click', function () {
                    debugger;
                });
            },
            _DuplicatePost: function (self) {
                $('.mpblog-post-duplicate').on('click', function () {
                    debugger;
                });
            },
            _DeletePost: function (self) {
                $('.mpblog-post-delete').on('click', function () {
                    debugger;
                });
            }
        }
    );

    return $.mageplaza.mpBlogManagePost;
});
