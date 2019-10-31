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
    "mage/adminhtml/events",
    "mage/adminhtml/wysiwyg/tiny_mce/setup"
], function ($, $t, _, modal) {
    'use strict';

    $.widget('mageplaza.mpBlogPostAction', {
            options: {

            },
            _create: function () {
                var self      = this;

                $('button.mpblog-action-new').on('click', function () {
                    self._AddNew(self);
                });
            },
            _AddNew: function (self) {
                var form = $('#mp_blog_post_form'),
                    formData = new FormData(form[0]),
                    htmlPopup = $('#mp-blog-new-post-popup'),
                    url = form.attr('action');

                $.ajax({
                    url: url,
                    type: "post",
                    data: formData,
                    cache: false,
                    contentType: false,
                    processData: false,
                    showLoader: true,
                    success: function (result) {
                        if (result.status === 1){
                            htmlPopup.data('mageModal').closeModal();
                        }
                    },
                    complete: function () {

                    }
                });
            }
        }
    );

    return $.mageplaza.mpBlogPostAction;
});
