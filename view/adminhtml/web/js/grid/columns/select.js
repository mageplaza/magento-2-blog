/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license sliderConfig is
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
    'Magento_Ui/js/grid/columns/select'
], function (Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html'
        },
        getLabel: function (record) {
            var label = this._super(record);

            if (label !== '') {
                switch (record.status) {
                    case '1':
                        label = '<span class="grid-severity-notice"><span>' + label + '</span></span>';
                        break;
                    case '2':
                        label = '<span class="grid-severity-critical"><span>' + label + '</span></span>';
                        break;
                    case '3':
                        label = '<span class="grid-severity-minor"><span>' + label + '</span></span>';
                        break;
                }
            }
            return label;
        }
    });
});