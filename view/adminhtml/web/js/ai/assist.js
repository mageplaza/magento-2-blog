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
    'uiRegistry',
    'mage/translate',
    'Mageplaza_Blog/js/ai/client'
], function ($, _, registry, $t, client) {
    'use strict';

    var EDITOR_ID = 'post_post_content';

    /**
     * @returns {String}
     */
    function getContent() {
        var editor = window.tinyMCE && window.tinyMCE.get(EDITOR_ID);

        return editor ? editor.getContent() : $('#' + EDITOR_ID).val() || '';
    }

    /**
     * @returns {String}
     */
    function getStoreIds() {
        var value = $('#post_store_ids').val();

        return _.isArray(value) ? value.join(',') : value || '';
    }

    /**
     * @param {String} selector
     * @param {String} value
     * @param {String} confirmText
     * @returns {Boolean}
     */
    function setField(selector, value, confirmText) {
        var $field = $(selector);

        if ($field.val() && !window.confirm(confirmText)) {
            return false;
        }
        $field.val(value).trigger('change');

        return true;
    }

    /**
     * @param {String} scope
     * @param {Number} id
     * @returns {Boolean}
     */
    function isSelected(scope, id) {
        var component = registry.get(scope);

        return !!component && _.contains(_.map(component.value(), String), String(id));
    }

    /**
     * @param {String} scope
     * @param {Number} id
     * @returns {Boolean}
     */
    function selectTerm(scope, id) {
        var component = registry.get(scope);

        if (!component || isSelected(scope, id)) {
            return false;
        }
        component.value(_.map(component.value(), String).concat(String(id)));

        return true;
    }

    return function (config, element) {
        var $root = $(element),
            $buttons = $root.find('[data-ai-type]'),
            $message = $root.find('.mpblog-ai-message'),
            $result = $root.find('.mpblog-ai-result');

        /**
         * @param {String} title
         * @returns {jQuery}
         */
        function section(title) {
            var $section = $('<div class="mpblog-ai-section"/>');

            if (title) {
                $('<h4/>').text(title).appendTo($section);
            }

            return $section.appendTo($result);
        }

        /**
         * @param {String} text
         * @returns {jQuery}
         */
        function note(text) {
            return $('<p class="mpblog-ai-note"/>').text(text);
        }

        /**
         * @param {String} shortDescription
         */
        function renderShort(shortDescription) {
            var $section = section($t('Short description'));

            $('<div class="mpblog-ai-short"/>').text(shortDescription).appendTo($section);
            $('<button type="button" class="action-default"/>').append($('<span/>').text($t('Use this description')))
                .on('click', function () {
                    if (setField('#post_short_description', shortDescription,
                        $t('Replace the current Short Description?'))) {
                        client.message($message, 'success', $t('Short description filled in.'));
                    }
                }).appendTo($section);
        }

        /**
         * @param {Array} titles
         */
        function renderTitles(titles) {
            var $section = section($t('5 titles (click to set as Name)')),
                $list = $('<ul class="mpblog-ai-list"/>').appendTo($section);

            _.each(titles, function (title) {
                $('<li/>').append($('<span/>').text(title))
                    .append($('<small/>').text($t('%1 chars').replace('%1', title.length)))
                    .on('click', function () {
                        if (setField('#post_name', title, $t('Replace the current Name?'))) {
                            client.message($message, 'success', $t('Name updated. URL Key is unchanged.'));
                        }
                    }).appendTo($list);
            });
            note($t('The URL Key is not changed.')).appendTo($section);
        }

        /**
         * @param {Object} response
         */
        function renderTaxonomy(response) {
            var $section = section(''),
                groups = {
                    tags: {scope: 'blogTag.blog_select_tag', title: $t('Tags')},
                    topics: {scope: 'blogTopic.blog_select_topic', title: $t('Topics')}
                },
                found = false;

            _.each(groups, function (info, key) {
                var group = response[key] || {},
                    $chips;

                if (_.isEmpty(group.existing) && _.isEmpty(group['new'])) {
                    return;
                }
                found = true;
                $('<h4/>').text(info.title).appendTo($section);
                $chips = $('<div class="mpblog-ai-chips"/>').appendTo($section);
                _.each(group.existing || [], function (item) {
                    var $chip = $('<span class="mpblog-ai-chip"/>').text(item.name)
                        .attr('title', $t('Existing, click to add')).appendTo($chips);

                    if (isSelected(info.scope, item.id)) {
                        $chip.addClass('_on');

                        return;
                    }
                    $chip.on('click', function () {
                        if (selectTerm(info.scope, item.id)) {
                            $chip.addClass('_on').off('click');
                        }
                    });
                });
                _.each(group['new'] || [], function (name) {
                    $('<span class="mpblog-ai-new"/>').text($t('%1 (new)').replace('%1', name)).appendTo($chips);
                });
            });
            if (!found) {
                $section.remove();

                return;
            }
            note($t('Blue chips are existing and can be added with one click. ' +
                'Plain text is a new suggestion and is not created automatically.')).appendTo($section);
        }

        /**
         * @param {Object} response
         */
        function render(response) {
            $result.empty();
            if (response.short_description) {
                renderShort(response.short_description);
            }
            if (!_.isEmpty(response.titles)) {
                renderTitles(response.titles);
            }
            if (response.tags || response.topics) {
                renderTaxonomy(response);
            }
            if (!$result.children().length) {
                $('<div/>').text($t('No suggestions found.')).appendTo($result);
            }
        }

        $buttons.on('click', function () {
            $result.empty();
            $message.empty().append($('<span class="mpblog-ai-loading"/>').text($t('Generating...')));
            client.post(config.url, {
                type: $(this).data('ai-type'),
                name: $('#post_name').val(),
                content: getContent(),
                'store_ids': getStoreIds()
            }, $buttons).done(function (response) {
                $message.empty();
                render(response);
            }).fail(function (text) {
                client.message($message, 'error', text);
            });
        });
    };
});
