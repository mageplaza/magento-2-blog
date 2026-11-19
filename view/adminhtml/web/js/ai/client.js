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
    'mage/translate'
], function ($, $t) {
    'use strict';

    var lastClicked = null;

    document.addEventListener('click', function (event) {
        lastClicked = event.target.closest ? event.target.closest('button') : null;
    }, true);

    return {
        /**
         * @param {jQuery} $el
         * @param {String} type
         * @param {String} text
         */
        message: function ($el, type, text) {
            $el.empty();
            if (text) {
                $('<div class="messages"/>').append(
                    $('<div/>').addClass('message message-' + type).append($('<div/>').text(text))
                ).appendTo($el);
            }
        },

        /**
         * @param {String} url
         * @param {Object} data
         * @param {jQuery} buttons
         * @returns {jQuery.Deferred}
         */
        post: function (url, data, buttons) {
            var deferred = $.Deferred(),
                $active = buttons.filter(lastClicked);

            buttons.prop('disabled', true).addClass('disabled');
            $active.addClass('mpblog-ai-busy');
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                data: $.extend({}, data, {'form_key': window.FORM_KEY})
            }).done(function (response) {
                if (response && response.success) {
                    deferred.resolve(response);
                } else {
                    deferred.reject(response && response.message ? response.message : $t('Request failed.'));
                }
            }).fail(function () {
                deferred.reject($t('Request failed.'));
            }).always(function () {
                buttons.prop('disabled', false).removeClass('disabled');
                $active.removeClass('mpblog-ai-busy');
            });

            return deferred.promise();
        }
    };
});
