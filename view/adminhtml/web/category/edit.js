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
], function (jQuery) {
    'use strict';

    var categorySubmit = function () {
        var activeTab = $('active_tab_id');
        if (activeTab) {
            if (activeTab.tabsJsObject && activeTab.tabsJsObject.tabs('activeAnchor')) {
                activeTab.value = activeTab.tabsJsObject.tabs('activeAnchor').prop('id');
            }
        }

        // Submit form
        jQuery('#category_edit_form').trigger('submit');
    };

    /**
     * @param {Object} config
     */
    function bootstrap(config) {
        require([
            'jquery',
            config.tinymceModule || 'tinymce',
            'loadingPopup',
            'mage/backend/floating-header'
        ], function (jQuery, tinyMCE) {

            /**
             * Update Category content area
             */
            function updateContent(url, params, refreshTree) {
                var node = tree.getNodeById(tree.currentNodeId),
                    parentNode = node && node.parentNode,
                    parentId,
                    redirectUrl;

                params = jQuery.extend(params || {}, {
                    form_key: FORM_KEY
                });

                if (params.node_name) {
                    node.setText(params.node_name);
                }

                (function ($) {
                    var $categoryContainer = $('#category-edit-container'),
                        messagesContainer = $('.messages');
                    messagesContainer.html('');
                    $.ajax({
                        url: url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true'),
                        data: params,
                        context: $('body'),
                        showLoader: true
                    }).done(function (data) {
                        if (data.content) {
                            var pageActions = $('.page-actions');
                            for (var i = 0; i < pageActions.length; i++){
                                if ($(pageActions[i]).data('floatingHeader')) {
                                    $(pageActions[i]).floatingHeader('destroy');
                                }
                            }
                            try {
                                $('#category-edit-container .wysiwyg-editor').each(function (index, element) {
                                    tinyMCE.execCommand('mceRemoveControl', false, $(element).attr('id'));
                                });
                                $categoryContainer.html('');
                            } catch (e) {
                                alert(e.message);
                            }
                            $categoryContainer.html(data.content).trigger('contentUpdated');
                            setTimeout(function () {
                                $('#category-edit-container .wysiwyg-editor').each(function (index, element) {
                                    tinyMCE.execCommand('mceRemoveControl', false, $(element).attr('id'));
                                    tinyMCE.execCommand('mceAddControl', true, $(element).attr('id'));
                                });
                                $('.page-actions').floatingHeader({
                                    'title': '.category-edit-title'
                                });
                                $('body').trigger('contentUpdated');
                                try {
                                    if (refreshTree) {
                                        window.refreshTreeArea();
                                    }
                                } catch (e) {
                                    alert(e.message);
                                }
                            }, 25);
                        }

                        if (data.messages && data.messages.length > 0) {
                            messagesContainer.html(data.messages);
                        }
                        if (data.toolbar) {
                            $('[data-ui-id="page-actions-toolbar-content-header"]').replaceWith(data.toolbar)
                        }
                    });
                })(jQuery);
            }

            /**
             * Reset Category form/content area
             */
            function categoryReset(url, useAjax) {
                if (useAjax) {
                    var params = {active_tab_id: false};
                    updateContent(url, params);
                } else {
                    location.href = url;
                }
            }

            function categoryDelete(url, message) {
                if (confirm(message)) {
                    location.href = url;
                }
            }

            /**
             * Refresh tree nodes after saving or deleting a category
             */
            function refreshTreeArea(transport) {
                var config,
                    url;

                if (tree && window.editingCategoryBreadcrumbs) {
                    if (tree.nodeForDelete) {
                        var node           = tree.getNodeById(tree.nodeForDelete);
                        tree.nodeForDelete = false;

                        if (node) {
                            node.parentNode.removeChild(node);
                            tree.currentNodeId = false;
                        }
                    }
                    else if (tree.addNodeTo) {
                        var parent     = tree.getNodeById(tree.addNodeTo);
                        tree.addNodeTo = false;

                        if (parent) {
                            config = editingCategoryBreadcrumbs[editingCategoryBreadcrumbs.length - 1];

                            window.location.href = tree.buildUrl(config.id);
                        }
                    }
                    for (var i = 0; i < editingCategoryBreadcrumbs.length; i++){
                        var node = tree.getNodeById(editingCategoryBreadcrumbs[i].id);
                        if (node) {
                            node.setText(editingCategoryBreadcrumbs[i].text);
                        }
                    }
                }
            }

            function displayLoadingMask() {
                jQuery('body').loadingPopup();
            }

            window.refreshTreeArea    = refreshTreeArea;
            window.updateContent      = updateContent;
            window.categoryDelete     = categoryDelete;
            window.categoryReset      = categoryReset;
            window.displayLoadingMask = displayLoadingMask;
        });
    }

    return function (config, element) {
        config = config || {};

        switch (config.action) {
            case 'bootstrap':
                bootstrap(config);
                break;

            case 'delete':
                jQuery(element).on('click', function () {
                    window.categoryDelete(config.url, config.message);
                });
                break;

            case 'reset':
                jQuery(element).on('click', function () {
                    window.categoryReset(config.url, Boolean(config.useAjax));
                });
                break;

            default:
                jQuery(element).on('click', function (event) {
                    categorySubmit(config.url, config.ajax);
                });
        }
    };
});
