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
(function () {
    var configEl = document.getElementById('mpblog-author-post-config');
    var config = configEl ? JSON.parse(configEl.textContent) : {};

    var pubUrl = config.pubUrl || '';
    var deleteUrl = config.deleteUrl || '';
    var listData = config.posts || {};

    var title = document.querySelector('#popup_author_post');
    var nameEle = document.querySelector('#name');
    var postIdEle = document.querySelector('#post_id');
    var shortDescriptionEle = document.querySelector('#short_description');
    var allowCommentEle = document.querySelector('#allow_comment');
    var publishDateEle = document.querySelector('#publish_date');

    function handleDelete(element) {
        var postId = element.closest('.post-info-action').getAttribute('data-postId');
        fetch(deleteUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                post_id: postId
            })
        })
            .then(function (response) { return response.json(); })
            .then(function (result) {
                if (result.status === 1) {
                    var postListItem = document.querySelector('.post-list-item[data-post-id="' + result.post_id + '"]');
                    if (postListItem) {
                        postListItem.remove();
                        var reloadCustomerDataEvent = new CustomEvent('reload-customer-section-data');
                        window.dispatchEvent(reloadCustomerDataEvent);
                    }
                }
            })
            .catch(function (error) { console.error('Error:', error); });
    }

    // open modal add new post
    function handleAddPost() {
        resetFormModal();
        title.innerText = 'Add New Post';
        openDialogNewPost();
    }

    // open modal edit new post
    function handleEdit(e, post_id, image) {
        resetFormModal();
        var dataSelect = listData[post_id];
        nameEle.value = dataSelect ? dataSelect.name : undefined;
        postIdEle.value = dataSelect ? dataSelect.post_id : undefined;
        shortDescriptionEle.value = dataSelect ? dataSelect.short_description : undefined;
        editor.setData((dataSelect && dataSelect.post_content) || '');
        allowCommentEle.value = dataSelect ? dataSelect.allow_comment : undefined;
        publishDateEle.value = convertDateToDisplay(dataSelect ? dataSelect.publish_date : undefined);
        treeselectCategories.updateValue(dataSelect ? dataSelect.category_ids : undefined);
        treeselectTags.updateValue(dataSelect ? dataSelect.tag_ids : undefined);
        treeselectTopics.updateValue(dataSelect ? dataSelect.topic_ids : undefined);
        addPreviewImg(image);

        title.innerText = 'Edit Post';
        openDialogNewPost();
    }

    // open modal duplicate new post
    function handleDuplicate(e, post_id, image) {
        resetFormModal();
        var dataSelect = listData[post_id];
        nameEle.value = dataSelect ? dataSelect.name : undefined;
        shortDescriptionEle.value = dataSelect ? dataSelect.short_description : undefined;
        editor.setData((dataSelect && dataSelect.post_content) || '');
        allowCommentEle.value = dataSelect ? dataSelect.allow_comment : undefined;
        publishDateEle.value = convertDateToDisplay(dataSelect ? dataSelect.publish_date : undefined);
        treeselectCategories.updateValue(dataSelect ? dataSelect.category_ids : undefined);
        treeselectTags.updateValue(dataSelect ? dataSelect.tag_ids : undefined);
        treeselectTopics.updateValue(dataSelect ? dataSelect.topic_ids : undefined);
        addPreviewImg(image);
        title.innerText = 'Duplicate Post';
        openDialogNewPost();
    }

    // reset data when hide modal
    function resetFormModal() {
        nameEle.value = '';
        postIdEle.value = '';
        shortDescriptionEle.value = '';
        editor.setData('');
        allowCommentEle.value = 0;
        publishDateEle.value = '';
        treeselectCategories.updateValue([]);
        treeselectTags.updateValue([]);
        treeselectTopics.updateValue([]);
        var element = document.querySelector('.mp-image-link');
        if (element) {
            element.parentNode.removeChild(element);
        }
        var messagesError = document.querySelectorAll('#mp-blog-new-post-popup form .messages');
        messagesError.forEach(function (mess) {
            mess.remove();
        });
    }

    // add preview image when open modal edit and duplicate modal
    function addPreviewImg(value) {
        if (value) {
            var imageEl = document.getElementById('image') ? document.getElementById('image').parentElement : null;
            var linkElement = document.createElement('a');
            linkElement.className = 'mp-image-link';
            linkElement.style = 'margin-right: 15px';
            linkElement.href = pubUrl + 'mageplaza/blog/post/' + value;
            linkElement.onclick = function () {
                imagePreview('post_image_image');
                return false;
            };
            var imageElement = document.createElement('img');
            imageElement.src = pubUrl + 'mageplaza/blog/post/' + value;
            imageElement.id = 'post_image_image';
            imageElement.title = value;
            imageElement.alt = value;
            imageElement.height = 40;
            imageElement.width = 40;
            imageElement.className = 'small-image-preview v-middle';
            linkElement.appendChild(imageElement);
            if (imageEl) {
                imageEl.insertBefore(linkElement, imageEl.children[0]);
            }
            var deleteSpan = document.createElement('span');
            deleteSpan.className = 'delete-image';
            deleteSpan.style.display = 'inline-flex';
            var deleteInput = document.createElement('input');
            deleteInput.type = 'checkbox';
            deleteInput.name = 'image[delete]';
            deleteInput.value = '1';
            deleteInput.className = 'checkbox';
            deleteInput.id = 'post_image_delete';

            var deleteLabel = document.createElement('label');
            deleteLabel.htmlFor = 'post_image_delete';
            deleteLabel.textContent = 'Delete Image';

            deleteSpan.appendChild(deleteInput);
            deleteSpan.appendChild(deleteLabel);
            if (imageEl && !imageEl.querySelector('.delete-image')) {
                imageEl.appendChild(deleteSpan);
            }
        }
    }

    // convert date in modal
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

    window.handleDelete = handleDelete;
    window.handleAddPost = handleAddPost;
    window.handleEdit = handleEdit;
    window.handleDuplicate = handleDuplicate;

    window.addEventListener('alpine:init', function () {
        window.Alpine.data('mpBlogPostActions', function () {
            return {
                handleEdit: handleEdit,
                handleDuplicate: handleDuplicate,
                handleDelete: handleDelete
            };
        });
    }, { once: true });
})();
