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
    'mpDevbridgeAutocomplete'
], function ($) {
    'use strict';

    return function (config, element) {
        var visibleImage = config.visibleImage;

        $(element).autocomplete({
            serviceUrl: config.serviceUrl,
            paramName: 'query',
            type: 'GET',
            deferRequestBy: 200,
            maxHeight: 2000,
            minChars: config.minChars,
            autoSelectFirst: true,
            showNoSuggestionNotice: true,
            triggerSelectOnValidInput: false,
            onSelect: function (suggestion) {
                window.location.href = suggestion.url;
            },
            formatResult: function (suggestion, currentValue) {
                var additionClass = '',
                    html          = "<div class='mpblog-suggestion'>";

                if (visibleImage) {
                    html += "<div class='mpblog-suggestion-left'><img class='img-responsive' src='" + suggestion.image + "' /></div>";
                    additionClass = 'image-visible';
                }

                html += "<div class='mpblog-suggestion-right " + additionClass + "'>" +
                    "<div class='mpblog-product-line mpblog-product-name'>" + suggestion.value + "</div>" +
                    "<div class='mpblog-product-des'><p class='mpblog-short-des'>" + suggestion.desc + "</p></div></div></div>";

                return html;
            }
        });
    };
});
