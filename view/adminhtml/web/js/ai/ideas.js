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
            $button = $('#mpblog-ideas-generate'),
            $refresh = $('#mpblog-ideas-refresh'),
            $buttons = $button.add($refresh),
            $message = $root.find('.mpblog-ai-message'),
            $head = $root.find('.mpblog-ai-head'),
            $hint = $root.find('.mpblog-ai-hint'),
            $result = $root.find('.mpblog-ai-result'),
            intents = {
                'how_to': $t('How-to'),
                comparison: $t('Comparison'),
                guide: $t('Guide'),
                review: $t('Review'),
                other: $t('Other')
            };

        /**
         * @param {String} text
         * @param {String} cssClass
         * @returns {jQuery}
         */
        function pill(text, cssClass) {
            return $('<span class="mpblog-ai-pill"/>').addClass(cssClass || '').text(text);
        }

        /**
         * @param {Object} topic
         * @returns {jQuery}
         */
        function renderTopic(topic) {
            var $item = $('<div class="mpblog-ai-card"/>'),
                $meta = $('<div class="mpblog-ai-meta"/>').appendTo($item),
                $terms = $('<div/>').appendTo($item),
                $foot;

            $('<h3/>').text(topic.title).prependTo($item);
            $('<p class="mpblog-ai-angle"/>').text(topic.angle).insertBefore($meta);
            $meta.append(pill($t('Intent: %1').replace('%1', intents[topic.intent] || intents.other)))
                .append(pill($t('Total searches: %1').replace('%1', Number(topic.total_searches).toLocaleString('en-US'))));
            if (topic.has_zero_result) {
                $meta.append(pill($t('Zero-result terms'), '_z'));
            }
            _.each(topic.terms, function (term) {
                $('<span class="mpblog-ai-term"/>').text(term).appendTo($terms);
            });
            if (!config.writeButton) {
                $foot = $('<div class="mpblog-ai-foot"/>').appendTo($item);
                $('<button type="button" class="action-default" disabled="disabled"/>')
                    .append($('<span/>').text($t('Write a post from this topic')))
                    .append($('<span class="mpblog-ai-badge"/>').text($t('PRO'))).appendTo($foot);
                $('<a target="_blank" rel="noopener"/>').attr('href', config.proUrl)
                    .text($t('Upgrade to Pro')).appendTo($foot);
            }

            return $item;
        }

        /**
         * @param {Object} response
         */
        function render(response) {
            var count = response.topics.length,
                $title;

            if (!count) {
                $result.text($t('No suggestions found.'));

                return;
            }
            $title = $('<b/>').text(config.maxTopics ? $t('%1 topics (top %2)').replace('%1', count)
                .replace('%2', config.maxTopics) : $t('%1 topics').replace('%1', count));
            $head.append($title);
            if (response.removed > 0) {
                $head.append($('<p class="mpblog-ai-note"/>')
                    .text($t('%1 terms removed because they may contain personal data.').replace('%1', response.removed)));
            }
            $head.append($('<p class="mpblog-ai-note"/>')
                .text($t('Results are cached for 24 hours. Click Run again to refresh.')));
            $result.addClass('mpblog-ai-cards');
            _.each(response.topics, function (topic) {
                $result.append(renderTopic(topic));
            });
        }

        /**
         * @param {Boolean} refresh
         */
        function run(refresh) {
            var $run = refresh ? $refresh : $button,
                $label = $run.find('span'),
                original = $label.text();

            $message.empty();
            $head.empty();
            $result.empty().removeClass('mpblog-ai-cards');
            $hint.hide();
            $label.text($t('Generating...'));
            $run.addClass('loading');
            client.post(config.url, {refresh: refresh ? 1 : 0}, $buttons).done(function (response) {
                render(response);
                $button.hide();
                $refresh.show();
            }).fail(function (text) {
                client.message($message, 'error', text);
                $hint.toggle(!refresh);
            }).always(function () {
                $run.removeClass('loading');
                $label.text(original);
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
