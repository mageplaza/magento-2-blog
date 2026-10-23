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
    'Magento_Ui/js/modal/confirm'
], function ($, confirm) {
    'use strict';

    return function (config, element) {
        config = config || {};

        $(element).on('click', function () {
            if (!config.url) {
                return;
            }

            if (!config.confirm) {
                location.href = config.url;

                return;
            }

            confirm({
                title: config.confirm.title,
                content: config.confirm.message,
                actions: {
                    confirm: function () {
                        location.href = config.url;
                    }
                }
            });
        });
    };
});
