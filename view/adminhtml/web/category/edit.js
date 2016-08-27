/**
 * Mageplaza_Blog extension
 *                     NOTICE OF LICENSE
 *
 *                     This source file is subject to the MIT License
 *                     that is bundled with this package in the file LICENSE.txt.
 *                     It is also available through the world-wide-web at this URL:
 *                     http://opensource.org/licenses/mit-license.php
 *
 *                     @category  Mageplaza
 *                     @package   Mageplaza_Blog
 *                     @copyright Copyright (c) 2016
 *                     @license   http://opensource.org/licenses/mit-license.php MIT License
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
    for (var i=0; i<fields.length; i++) {
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
    if (parentId == categoryId) { // Maybe path includes Category id itself
        parentId = path.pop();
    }

    // Make operations with Category tree
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

