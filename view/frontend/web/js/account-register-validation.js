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
    'mage/mage'
], function ($) {
    'use strict';

    return function (config, element) {
        var dataForm = $(element),
            options = {
                ignore: config.dobEnabled ? ':hidden:not(input[id$="full"])' : ':hidden'
            };

        if (config.dobEnabled) {
            options.errorPlacement = function (error, errorElement) {
                var dobElement,
                    errorClass;

                if (errorElement.prop('id').search('full') !== -1) {
                    dobElement = $(errorElement).parents('.customer-dob');
                    errorClass = error.prop('class');
                    error.insertAfter(errorElement.parent());
                    dobElement.find('.validate-custom').addClass(errorClass)
                        .after('<div class="' + errorClass + '"></div>');
                } else {
                    error.insertAfter(errorElement);
                }
            };
        }

        dataForm.mage('validation', options).find('input:text').attr('autocomplete', 'off');
    };
});
