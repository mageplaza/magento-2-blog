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

    return {
        /**
         * @param {String} url
         * @param {Object} data
         * @param {jQuery} buttons
         * @returns {jQuery.Deferred}
         */
        post: function (url, data, buttons) {
            var deferred = $.Deferred();

            buttons.prop('disabled', true).addClass('disabled');
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
            });

            return deferred.promise();
        }
    };
});
