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
    'prototype'
], function (jQuery) {

    var categorySubmit = function (url, useAjax) {
        var activeTab = $('active_tab_id');
        if (activeTab) {
            if (activeTab.tabsJsObject && activeTab.tabsJsObject.tabs('activeAnchor')) {
                activeTab.value = activeTab.tabsJsObject.tabs('activeAnchor').prop('id');
            }
        }

        var params = {};
        var fields = $('category_edit_form').getElementsBySelector('input', 'select');
        for (var i = 0; i < fields.length; i++) {
            if (!fields[i].name) {
                continue;
            }
            params[fields[i].name] = fields[i].getValue();
        }

        // Get info about what we're submitting - to properly update tree nodes
        var categoryId = params['category[id]'] ? params['category[id]'] : 0;
        var isCreating = categoryId == 0; // Separate variable is needed because '0' in javascript converts to TRUE
        var path = params['category[path]'].split('/');
        var parentId = path.pop();
        if (parentId == categoryId) { // Maybe path includes Blog Category id itself
            parentId = path.pop();
        }

        // Make operations with Blog Category tree
        if (isCreating) {
            if (!tree.currentNodeId) {
                // First submit of form - select some node to be current
                tree.currentNodeId = parentId;
            }
            tree.addNodeTo = parentId;
        }

        // Submit form
        jQuery('#category_edit_form').trigger('submit');
    };

    return function (config, element) {
        config = config || {};
        jQuery(element).on('click', function (event) {
            categorySubmit(config.url, config.ajax);
        });
    };
});
