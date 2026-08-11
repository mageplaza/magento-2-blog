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
    'jquery'
], function ($) {
    'use strict';

    return function (config) {
        $(window).on('load', function () {
            var body = $('body');

            if (body.hasClass('page-layout-1column')) {
                $('.columns').find('.sidebar.sidebar-main').remove();
            }

            if (config.isEthemeYourstore) {
                body.addClass('mpblog-etheme-yourstore');
            }
        });
    };
});
