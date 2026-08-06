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

define([], function () {
    'use strict';

    return function (config) {
        window.loginUrl = config.loginUrl;
        window.like = config.like;
        window.reply = config.reply;
        window.isLogged = config.isLogged;
        window.likedColor = config.likedColor;
        window.messengerBox = config.messengerBox;

        require(['comment']);
    };
});
