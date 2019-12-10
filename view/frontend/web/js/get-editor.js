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
    "mage/adminhtml/events",
    "mage/adminhtml/wysiwyg/tiny_mce/setup"
], function ($) {
    'use strict';

    return {
        config: function (nameEL, versionEditor, versionMagento, width) {
            var wysiwygcompany_description,
                config = {},
                editor;

            if (typeof width === 'undefined') {
                width = '99%';
            }

            if (versionMagento === "2") {
                $.extend(config, {
                    settings: {
                        theme_advanced_buttons1: 'bold,italic,|,justifyleft,justifycenter,justifyright,|,' +
                            'fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,' +
                            'bullist,numlist,|,code',
                        theme_advanced_buttons2: null,
                        theme_advanced_buttons3: null,
                        theme_advanced_buttons4: null
                    }
                });
                editor = new tinyMceWysiwygSetup(
                    nameEL,
                    config
                );
                editor.turnOn();
                $('#' + nameEL).addClass('wysiwyg-editor').data('wysiwygEditor', editor);
            } else if (versionEditor === "4") {
                wysiwygcompany_description = new wysiwygSetup(nameEL, {
                    "width": width,
                    "height": "200px",
                    "plugins": [{"name": "image"}],
                    "tinymce4": {
                        "toolbar": "formatselect | bold italic underline | alignleft aligncenter alignright |" +
                            " bullist numlist | link table charmap",
                        "plugins": "advlist autolink lists link charmap media noneditable" +
                            " table contextmenu paste code help table"
                    }
                });
                wysiwygcompany_description.setup("exact");
            } else {
                $.extend(config, {
                    settings: {
                        theme_advanced_buttons1: 'bold,italic,|,justifyleft,justifycenter,justifyright,|,' +
                            'fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,' +
                            'bullist,numlist,|,code',
                        theme_advanced_buttons2: null,
                        theme_advanced_buttons3: null,
                        theme_advanced_buttons4: null
                    }
                });
                editor = new wysiwygSetup(
                    nameEL,
                    config
                );
                editor.setup("exact");
                $('#' + nameEL).addClass('wysiwyg-editor').data('wysiwygEditor', editor);
            }
        }
    };
});