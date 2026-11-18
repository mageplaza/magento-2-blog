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
     */
    function setField(selector, value) {
        var $field = $(selector);

        if ($field.val() && !window.confirm($t('Replace the current value?'))) {
            return;
        }
        $field.val(value).trigger('change');
    }

    /**
     * @param {String} scope
     * @param {Number} id
     * @returns {Boolean}
     */
    function selectTerm(scope, id) {
        var component = registry.get(scope),
            current = component ? _.map(component.value(), String) : [];

        if (!component) {
            return false;
        }
        if (_.contains(current, String(id))) {
            return false;
        }
        component.value(current.concat(String(id)));

        return true;
    }

    return function (config, element) {
        var $root = $(element),
            $buttons = $root.find('[data-ai-type]'),
            $message = $root.find('.mpblog-ai-message'),
            $result = $root.find('.mpblog-ai-result');

        /**
         * @param {String} label
         * @param {Function} handler
         * @returns {jQuery}
         */
        function addButton(label, handler) {
            return $('<button type="button" class="action-default"/>').text(label).on('click', handler)
                .appendTo($('<div/>').appendTo($result)).parent();
        }

        /**
         * @param {Object} response
         */
        function render(response) {
            $result.empty();
            if (response.short_description) {
                addButton(response.short_description, function () {
                    setField('#post_short_description', response.short_description);
                });
            }
            _.each(response.titles || [], function (title) {
                addButton(title, function () {
                    setField('#post_name', title);
                });
            });
            _.each({tags: 'blogTag.blog_select_tag', topics: 'blogTopic.blog_select_topic'}, function (scope, key) {
                var group = response[key];

                if (!group) {
                    return;
                }
                _.each(group.existing, function (item) {
                    if (selectTerm(scope, item.id)) {
                        $('<div/>').text($t('Selected: %1').replace('%1', item.name)).appendTo($result);
                    }
                });
                if (group.new.length) {
                    $('<div/>').text($t('New %1 (not created): %2').replace('%1', key)
                        .replace('%2', group.new.join(', '))).appendTo($result);
                }
            });
            if (!$result.children().length) {
                $('<div/>').text($t('No suggestions found.')).appendTo($result);
            }
        }

        $buttons.on('click', function () {
            $message.text('');
            $result.empty();
            client.post(config.url, {
                type: $(this).data('ai-type'),
                name: $('#post_name').val(),
                content: getContent(),
                'store_ids': getStoreIds()
            }, $buttons).done(render).fail(function (text) {
                $message.text(text);
            });
        });
    };
});
