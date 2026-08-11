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
    'Mageplaza_Blog/js/get-editor'
], function ($, editor) {
    'use strict';

    return function (config, element) {
        $(element).on('keyup', function () {
            var url = config.authorUrlBase + $(this).val() + config.urlSuffix;

            if ($(this).val() === '') {
                url = '';
            }
            $('.mp_full_url').html(url);
        });

        editor.config(
            'short_description',
            config.editorVersion,
            config.magentoVersion,
            '99%'
        );
    };
});
