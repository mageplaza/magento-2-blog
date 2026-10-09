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

(function () {
    var configEl = document.getElementById('mpblog-view-counter-config');
    var config = configEl ? JSON.parse(configEl.textContent) : {};

    if (!config.postId || !config.url) {
        return;
    }

    var data = new FormData();

    data.append('post_id', config.postId);
    fetch(config.url, {
        method: 'POST',
        body: data,
        credentials: 'same-origin',
        keepalive: true
    }).catch(function () {});
})();
