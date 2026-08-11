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

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([], factory);
    } else {
        factory();
    }
}(function () {
    'use strict';

    if (window.__mpBlogShareBound) {
        return;
    }
    window.__mpBlogShareBound = true;

    /**
     * @param {string} url
     * @param {string} text
     */
    function openTwitterShare(url, text) {
        window.open(
            'https://twitter.com/share?text=' + text + '&url=' + url,
            'Twitter-dialog',
            'width=626,height=436'
        );
    }

    /**
     * @param {string} url
     */
    function openFacebookShare(url) {
        window.open(
            'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url),
            'facebook-share-dialog',
            'width=626,height=436'
        );
    }

    document.addEventListener('click', function (event) {
        var trigger = event.target.closest && event.target.closest('[data-share]');

        if (!trigger) {
            return;
        }

        var network = trigger.getAttribute('data-share');
        var url = trigger.getAttribute('data-share-url') || '';
        var text = trigger.getAttribute('data-share-text') || '';

        if (network === 'twitter') {
            event.preventDefault();
            openTwitterShare(url, text);

            return;
        }

        if (network === 'facebook') {
            event.preventDefault();
            openFacebookShare(url);
        }
    });
}));
