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
         * @returns {jQuery}
         */
        panelButtons: function () {
            return $('.mpblog-ai-assist button').not('[data-ai-cancel]');
        },

        /**
         * @param {jQuery} locked
         * @param {jQuery} active
         */
        unlock: function (locked, active) {
            locked.prop('disabled', false).removeClass('disabled');
            if (active) {
                active.removeClass('mpblog-ai-busy');
            }
        },

        /**
         * @param {String} url
         * @param {Object} data
         * @param {jQuery} buttons
         * @param {Object} options
         * @returns {jQuery.Deferred}
         */
        post: function (url, data, buttons, options) {
            var deferred = $.Deferred(),
                self = this,
                $active = buttons.filter(lastClicked),
                $locked = buttons.add(this.panelButtons()).filter(function () {
                    return !this.disabled;
                }),
                keep = options && options.keepLock;

            $locked.prop('disabled', true).addClass('disabled');
            $active.addClass('mpblog-ai-busy');
            $.ajax({
                url: url,
                type: 'POST',
                dataType: 'json',
                global: false,
                error: $.noop,
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
                if (!keep) {
                    self.unlock($locked, $active);
                }
            });

            return $.extend(deferred.promise(), {locked: $locked});
        }
    };
});
