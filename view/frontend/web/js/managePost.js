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
    'Magento_Ui/js/modal/modal',
    'uiRegistry',
    "mage/adminhtml/events",
    "mage/adminhtml/wysiwyg/tiny_mce/setup"
], function ($, $t, _, modal, registry) {
    'use strict';

    $.widget('mageplaza.mpBlogManagePost', {
            options: {
                newUrl: '',
                duplicateUrl: '',
                deleteUrl: '',
                editUrl: '',
                basePubUrl: '',
                postDatas: {}
            },
            _create: function () {
                var self      = this,
                    htmlPopup = $('#mp-blog-new-post-popup');

                $('.mp-blog-new-post button').on('click', function () {
                    self._AddNewPost(self, htmlPopup);
                });

                $('.mpblog-post-edit').on('click', function () {
                    self._EditPost(self, this, htmlPopup);
                });

                $('.mpblog-post-duplicate').on('click', function () {
                    self._DuplicatePost(this);
                });

                $('.mpblog-post-delete').on('click', function () {
                    self._DeletePost(this);
                });
            },
            _AddNewPost: function (self, htmlPopup) {
                var options = {
                    'type': 'popup',
                    'title': $t('Add New Post'),
                    'responsive': true,
                    'innerScroll': true,
                    'buttons': []
                };
                self._openPopup(options, htmlPopup);
            },
            _EditPost: function (self, click, htmlPopup) {
                var postId   = $(click).parent().data('postid'),
                    postData = self.options.postDatas[postId],
                    pubUrl = self.options.basePubUrl,
                    options  = {
                        'type': 'popup',
                        'title': $t('Add New Post'),
                        'responsive': true,
                        'innerScroll': true,
                        'buttons': []
                    };
                self._openPopup(options, htmlPopup);

                _.each(postData, function (value, name) {
                    var field = htmlPopup.find('#mp_blog_post_form [name="' + name + '"]'),
                        imageEL,
                        category,
                        tag,
                        topic;

                    if (field.is('[type="file"]')) {
                        imageEL = '<a href="'+pubUrl+'mageplaza/blog/post/'+value+'" onclick="imagePreview(\'post_image_image\'); return false;" >' +
                                '<img src="'+pubUrl+'mageplaza/blog/post/'+value+'" id="post_image_image"' +
                                ' title="'+value+'" alt="'+value+'" height="22" width="22"' +
                                ' class="small-image-preview v-middle"></a>';
                        field.parent().prepend(imageEL);
                    } else if (field.is('input') || field.is('select')) {
                        field.val(value);
                    } else {
                        if (field.is('textarea'))
                            field.html(value);
                    }
                    if (name === 'categories_ids'){
                        category = registry.get('customCategory').value(value);
                    }
                    if (name === 'tags_ids' ){
                        tag = registry.async('customTag').value(value);
                    }
                    if (name === 'topics_ids'){
                        topic = registry.async('customTopic').value(value);
                    }
                });
                debugger;
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
            },
            _openPopup: function (options, htmlPopup) {
                var popupModal,
                    wysiwygcompany_description;

                popupModal = modal(options, htmlPopup);
                popupModal.openModal();
                $('#mp_blog_post_form').trigger('contentUpdated');

                wysiwygcompany_description = new wysiwygSetup("post_content", {
                    "width": "99%",
                    "height": "200px",
                    "plugins": [{"name": "image"}],
                    "tinymce4": {
                        "toolbar": "formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap",
                        "plugins": "advlist autolink lists link charmap media noneditable table contextmenu paste code help table"
                    }
                });
                wysiwygcompany_description.setup("exact");
            }
        }
    );

    return $.mageplaza.mpBlogManagePost;
});
