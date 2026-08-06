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

var configEl = document.getElementById('mpblog-manage-post-config');
var mpManagePostConfig = configEl ? JSON.parse(configEl.textContent) : {};

if (document.getElementById('post_content') && window.CKEDITOR && window.CKEDITOR.ClassicEditor) {
    CKEDITOR.ClassicEditor.create(document.getElementById('post_content'), {
        toolbar: {
            items: [
                'undo', 'redo', '|',
                'heading', '|',
                'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                'bulletedList', 'numberedList', 'todoList', '|',
                'alignment:left', 'alignment:right', 'alignment:center', 'alignment:justify', '|',
                'blockQuote', '|',
                'outdent', 'indent'
            ],
            shouldNotGroupWhenFull: true
        },
        list: {
            properties: {
                styles: true,
                startIndex: true,
                reversed: true
            }
        },
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading_heading1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
                { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' },
                { model: 'heading5', view: 'h5', title: 'Heading 5', class: 'ck-heading_heading5' },
                { model: 'heading6', view: 'h6', title: 'Heading 6', class: 'ck-heading_heading6' }
            ]
        },
        placeholder: '',
        removePlugins: [
            'AIAssistant',
            'CKBox',
            'CKFinder',
            'EasyImage',
            'RealTimeCollaborativeComments',
            'RealTimeCollaborativeTrackChanges',
            'RealTimeCollaborativeRevisionHistory',
            'PresenceList',
            'Comments',
            'TrackChanges',
            'TrackChangesData',
            'RevisionHistory',
            'Pagination',
            'WProofreader',
            'MathType',
            'SlashCommand',
            'Template',
            'DocumentOutline',
            'FormatPainter',
            'TableOfContents',
            'PasteFromOfficeEnhanced'
        ]
    }).then(function (editorInstance) {
        window.editor = editorInstance;
    }).catch(function (err) {
        console.error(err.stack || err);
    });
}

var categories = mpManagePostConfig.categories || [];
var topics = mpManagePostConfig.topics || {};
var tags = mpManagePostConfig.tags || {};
var optionsCategories = (categories || []).map(function (data) {
    return {
        name: data ? data.label : undefined,
        value: data ? data.value : undefined
    };
});
var optionsTopics = Object.values(topics || {}).map(function (data) {
    return {
        name: data ? data.label : undefined,
        value: data ? data.value : undefined
    };
});
var optionsTags = Object.values(tags || {}).map(function (data) {
    return {
        name: data ? data.label : undefined,
        value: data ? data.value : undefined
    };
});
var domElementCategories = document.querySelector('#categories_ids');
var treeselectCategories = new Treeselect({
    parentHtmlContainer: domElementCategories,
    options: optionsCategories,
    value: []
});
var domElementTopics = document.querySelector('#topics_ids');
var treeselectTopics = new Treeselect({
    parentHtmlContainer: domElementTopics,
    options: optionsTopics,
    value: []
});
var domElementTags = document.querySelector('#tags_ids');
var treeselectTags = new Treeselect({
    parentHtmlContainer: domElementTags,
    options: optionsTags,
    value: []
});
treeselectTags.srcElement.addEventListener('input', function (e) {
    tags_ids = e.detail ? e.detail.join(',') : '';
});

// convert date in modal (also duplicated in author-post.js — see note there)
function convertDateToSave(publicDateValue) {
    var inputDate = new Date(publicDateValue);
    return ('0' + (inputDate.getMonth() + 1)).slice(-2) + '/' +
        ('0' + inputDate.getDate()).slice(-2) + '/' +
        inputDate.getFullYear() + ' ' +
        ('0' + inputDate.getHours()).slice(-2) + ':' +
        ('0' + inputDate.getMinutes()).slice(-2) + ':' +
        ('0' + inputDate.getSeconds()).slice(-2);
}

function convertDateToDisplay(dateString) {
    var currentDate = new Date(dateString);
    var year = currentDate.getFullYear();
    var month = ('0' + (currentDate.getMonth() + 1)).slice(-2);
    var day = ('0' + currentDate.getDate()).slice(-2);
    var hours = ('0' + currentDate.getHours()).slice(-2);
    var minutes = ('0' + currentDate.getMinutes()).slice(-2);
    return year + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
}

function initAddNewPost() {
    return Object.assign(
        hyva.formValidation(document.getElementById('mp_blog_post_form')),
        {
            errors: 0,
            hasCaptchaToken: 0,
            showPassword: false,
            displayErrorMessage: false,
            errorMessages: [],
            setErrorMessages(messages) {
                this.errorMessages = [messages];
                this.displayErrorMessage = this.errorMessages.length;
            },
            dispatchAddNewPostRequest: function (form) {
                var formData = new FormData();
                var imageInput = document.querySelector('#image');
                if (imageInput && imageInput.files.length > 0) {
                    formData.append('image', imageInput.files[0]);
                } else {
                    var emptyBlob = new Blob([''], { type: 'application/octet-stream' });
                    formData.append('image', emptyBlob, 'empty_image.txt');
                }
                formData.append('name', document.querySelector('#name').value);
                formData.append('post_id', document.querySelector('#post_id').value);
                formData.append('short_description', document.querySelector('#short_description').value);
                formData.append('post_content', document.querySelector('#post_content').value);

                formData.append('categories_ids', treeselectCategories.value ? treeselectCategories.value.join(',') : '');
                formData.append('topics_ids', treeselectTopics.value ? treeselectTopics.value.join(',') : '');
                formData.append('tags_ids', treeselectTags.value ? treeselectTags.value.join(',') : '');
                formData.append('allow_comment', document.querySelector('#allow_comment').value);

                var publicDateValue = document.querySelector('#publish_date').value;
                if (publicDateValue) {
                    formData.append('publish_date', convertDateToSave(publicDateValue));
                } else {
                    formData.append('publish_date', '');
                }
                var checkboxElement = document.getElementById('post_image_delete');
                if (checkboxElement && checkboxElement.checked) {
                    formData.append(checkboxElement.name, checkboxElement.value);
                }
                var imgElement = document.getElementById('post_image_image');
                if (imgElement) {
                    formData.append('image', imgElement.title);
                }

                fetch(mpManagePostConfig.submitUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData,
                    mode: 'cors',
                    credentials: 'include'
                })
                    .then(function (response) { return response.json(); })
                    .then(function () {
                        window.location.reload();
                    })
                    .catch(function (error) { console.error('Error:', error); });
            },
            submitForm() {
                this.validate()
                    .then(() => {
                        if (this.errors === 0) {
                            this.dispatchAddNewPostRequest(document.querySelector('#mp_blog_post_form'));
                        }
                    })
                    .catch((invalid) => {
                        if (invalid.length > 0) {
                            invalid[0].focus();
                        }
                    });
            }
        }
    );
}

window.addEventListener('alpine:init', function () {
    window.Alpine.data('initAddNewPost', initAddNewPost);
}, { once: true });
