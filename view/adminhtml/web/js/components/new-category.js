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
    'underscore',
    'Magento_Catalog/js/components/new-category'
], function (_, Category) {
    'use strict';

    /**
     * Processing options list
     *
     * @param {Array} array - Property array
     * @param {String} separator - Level separator
     * @param {Array} created - list to add new options
     *
     * @return {Array} Plain options list
     */
    function flattenCollection(array, separator, created) {
        var i = 0,
            length,
            childCollection;

        array = _.compact(array);
        length = array.length;
        created = created || [];

        for (i; i < length; i++) {
            created.push(array[i]);

            if (array[i].hasOwnProperty(separator)) {
                childCollection = array[i][separator];
                delete array[i][separator];
                flattenCollection.call(this, childCollection, separator, created);
            }
        }

        return created;
    }

    return Category.extend({
        /**
         * Get path to current option
         *
         * @param {Object} data - option data
         * @returns {String} path
         */
        getPath: function (data) {
            var pathParts,
                createdPath = '';

            if (this.renderPath && typeof data.path !== "undefined") {
                pathParts = data.path.split('.');
                _.each(pathParts, function (curData) {
                    createdPath = createdPath ? createdPath + ' / ' + curData : curData;
                });

                return createdPath;
            }
        },

        /**
         * Set option to options array.
         *
         * @param {Object} option
         * @param {Array} options
         */
        setOption: function (option, options) {
            // eslint-disable-next-line radix
            var parent = parseInt(option.parent),
                copyOptionsTree;

            if (_.contains([0, 1], parent)) {
                options = options || this.cacheOptions.tree;
                options.push(option);

                copyOptionsTree = JSON.parse(JSON.stringify(this.cacheOptions.tree));
                this.cacheOptions.plain = flattenCollection(copyOptionsTree, this.separator);
                this.options(this.cacheOptions.tree);
            } else {
                this._super(option, options);
            }
        }
    });
});
