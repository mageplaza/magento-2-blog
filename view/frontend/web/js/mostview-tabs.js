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

    return function () {
        $('#popular-tab').click(function () {
            $('#tab-1').addClass('active');
            $('#tab-2').removeClass('active');
            $('#mostview').show();
            $('#mostrecent').hide();
        });
        $('#recent-tab').click(function () {
            $('#tab-1').removeClass('active');
            $('#tab-2').addClass('active');
            $('#mostview').hide();
            $('#mostrecent').show();
        });
    };
});
