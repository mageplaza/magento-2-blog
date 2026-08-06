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

/*jshint jquery:true browser:true*/
/*global Ajax:true alert:true*/
define([
    "jquery",
    "mage/backend/form",
    "jquery/ui",
    "prototype"
], function ($) {
    "use strict";

    $.widget("mage.categoryForm", $.mage.form, {
        options: {
            categoryIdSelector: 'input[name="category[category_id]"]',
            categoryPathSelector: 'input[name="category[path]"]',
            refreshUrl: '',
            tabsId: ''
        },

        /**
         * Form creation
         * @protected
         */
        _create: function () {
            this._super();
            $('body').on('categoryMove.tree', $.proxy(this.refreshPath, this));
            this._initUrlKeyDialogAndSubmit();
            this._initActiveTabTracking();
        },

        /**
         * Sending ajax to server to refresh field 'category[path]'
         * @protected
         */
        refreshPath: function () {
            var that = this;
            if (!this.element.find(this.options.categoryIdSelector).prop('value')) {
                return false;
            }
            $.ajax({
                type: 'POST',
                url: this.options.refreshUrl,
                dataType: 'json',
                data: {
                    form_key: FORM_KEY
                }
            }).success(function (data) {
                that._refreshPathSuccess(data);
            });
        },
        _refreshPathSuccess: function (response) {
            if (response.error) {
                alert(response.message);
            } else {
                if (this.element.find(this.options.categoryIdSelector).prop('value') == response.id) {
                    this.element.find(this.options.categoryPathSelector)
                        .prop('value', response.path);
                }
            }
        },

        /**
         * @private
         */
        _initUrlKeyDialogAndSubmit: function () {
            var element = this.element;

            require(['jquery', 'jquery/ui', 'mage/mage', 'mage/translate'], function ($) {
                var mageDialog = (function ($) {
                    var self = {dialogOpened: false, callback: [], needShow: false};

                    self.callback     = {ok: [], cancel: []};
                    self.createDialog = function () {
                        var onEvent = function (type, dialog) {
                            self.callback[type].forEach(function (call) {
                                call();
                            });
                            $(dialog).dialog("close");
                        };

                        self.dialog = $('[data-id="information-dialog-category"]').dialog({
                            autoOpen: false,
                            modal: true,
                            dialogClass: 'popup-window',
                            resizable: false,
                            width: '75%',
                            title: $.mage.__('Warning message'),
                            buttons: [{
                                text: $.mage.__('Ok'),
                                'class': 'action-primary',
                                click: function () {
                                    onEvent('ok', this);
                                }
                            }, {
                                text: $.mage.__('Cancel'),
                                'class': 'action-close',
                                click: function () {
                                    onEvent('cancel', this);
                                }
                            }],
                            open: function () {
                                $(this).closest('.ui-dialog').addClass('ui-dialog-active');

                                var topMargin = $(this).closest('.ui-dialog').children('.ui-dialog-titlebar').outerHeight() + 30;
                                $(this).closest('.ui-dialog').css('margin-top', topMargin);

                                self.dialogOpened = true;
                                self.callback.ok.push(function () {
                                    self.needShow = false;
                                });
                            },
                            close: function (event, ui) {
                                $(this).dialog('destroy');
                                self.dialogOpened = false;
                                self.callback     = {ok: [], cancel: []};
                                delete self.dialog;
                            }
                        });
                    };

                    return {
                        needToShow: function () {
                            self.needShow = true && !!$('[data-ui-id="tabs-tab-general-information-fieldset-element-hidden-general-id"]').length;
                            return this;
                        },
                        isNeedShow: function () {
                            return self.needShow;
                        },
                        onOk: function (call) {
                            self.callback.ok.push(call);
                            return this;
                        },
                        onCancel: function (call) {
                            self.callback.cancel.push(call);
                            return this;
                        },
                        show: function () {
                            if (self.dialog == undefined) {
                                self.createDialog();
                            }
                            if (self.dialogOpened == false) {
                                self.dialog.dialog('open');
                            }
                            return this;
                        }
                    };
                })(jQuery);

                $(document).on('change', '[data-ui-id="urlkeyrenderer-text-general-url-key"]', function () {
                    mageDialog.needToShow();
                });

                element.mage('validation', {
                    submitHandler: function (form) {
                        if (mageDialog.isNeedShow()) {
                            mageDialog.onOk(function () {
                                form.submit();
                                displayLoadingMask();
                            }).show();
                        } else {
                            form.submit();
                            displayLoadingMask();
                        }
                    }
                });
            });
        },

        /**
         * @private
         */
        _initActiveTabTracking: function () {
            var tabsId = this.options.tabsId;

            if (!tabsId) {
                return;
            }

            require(['jquery', 'mage/backend/tabs'], function ($) {
                var activeAnchor = $('#' + tabsId).tabs('activeAnchor');

                if (activeAnchor.length) {
                    $('active_tab_id').value = activeAnchor.prop('id');
                }
                $('active_tab_id').tabsJsObject = $('#' + tabsId);
            });
        }
    });

    return $.mage.categoryForm;
});
