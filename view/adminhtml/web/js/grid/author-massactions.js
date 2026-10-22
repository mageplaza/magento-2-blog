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
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/grid/tree-massactions'
], function ($, _, $t, confirm, alert, TreeMassactions) {
    'use strict';

    return TreeMassactions.extend({
        _confirm: function (action, callback) {
            var data = this.getSelections(),
                itemsType = data.excludeMode ? 'excluded' : 'selected',
                request = {form_key: window.FORM_KEY},
                parent = this._super.bind(this);

            if (!action.postCountUrl) {
                return parent(action, callback);
            }

            request[itemsType] = data[itemsType].length ? data[itemsType] : false;
            _.extend(request, data.params || {});

            $.ajax({
                url: action.postCountUrl,
                type: 'POST',
                dataType: 'json',
                data: request,
                showLoader: true
            }).done(function (result) {
                var content;

                if (!result || !result.total) {
                    return parent(action, callback);
                }

                content = $('<div/>')
                    .append($('<p/>').text(
                        $t('%1 post(s) will be deleted together with the selected authors:').replace('%1', result.total)
                    ));
                content.append($('<ul/>').append(_.map(result.authors, function (author) {
                    return $('<li/>').text(author.name + ': ' + author.count);
                })));
                content.append($('<p/>').text($t('Are you sure you want to continue?')));

                confirm({
                    title: action.confirm.title,
                    content: content.html(),
                    actions: {
                        confirm: callback
                    }
                });
            }).fail(function () {
                alert({
                    content: $t('Could not count the posts of the selected authors. Please try again.')
                });
            });
        }
    });
});
