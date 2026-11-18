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
    'underscore',
    'mage/translate',
    'Mageplaza_Blog/js/ai/client'
], function ($, _, $t, client) {
    'use strict';

    return function (config, element) {
        var $root = $(element),
            $button = $root.find('#mpblog-ideas-generate'),
            $refresh = $root.find('#mpblog-ideas-refresh'),
            $buttons = $button.add($refresh),
            $message = $root.find('.mpblog-ai-message'),
            $result = $root.find('.mpblog-ai-result');

        /**
         * @param {Object} topic
         * @returns {jQuery}
         */
        function renderTopic(topic) {
            var $item = $('<li class="mpblog-idea"/>'),
                searches = $t('%1 searches').replace('%1', topic.total_searches);

            $('<strong/>').text(topic.title).appendTo($item);
            $('<div/>').text(topic.angle).appendTo($item);
            $('<div class="admin__field-note"/>')
                .text(topic.intent + ' | ' + searches + (topic.has_zero_result ? ' | ' + $t('has zero-result terms') : '') +
                    ' | ' + topic.terms.join(', ')).appendTo($item);
            if (!config.writeButton) {
                $('<a class="action-default" target="_blank" rel="noopener"/>')
                    .attr('href', config.proUrl).attr('title', $t('Available in the Pro edition'))
                    .text($t('Write a post from this topic (Pro)')).appendTo($item);
            }

            return $item;
        }

        /**
         * @param {Object} response
         */
        function render(response) {
            var $list = $('<ul/>');

            if (!response.topics.length) {
                $result.text($t('No suggestions found.'));

                return;
            }
            _.each(response.topics, function (topic) {
                $list.append(renderTopic(topic));
            });
            $result.append($list);
        }

        /**
         * @param {Boolean} refresh
         */
        function run(refresh) {
            $message.text('');
            $result.empty();
            $button.addClass('loading');
            $result.text($t('Generating ideas...'));
            client.post(config.url, {refresh: refresh ? 1 : 0}, $buttons).done(function (response) {
                $result.empty();
                render(response);
                $refresh.show();
            }).fail(function (text) {
                $result.empty();
                $message.text(text);
            }).always(function () {
                $button.removeClass('loading');
            });
        }

        $button.on('click', function () {
            run(false);
        });
        $refresh.on('click', function () {
            run(true);
        });
    };
});
