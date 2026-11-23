(function () {
    var DIALOG = 'add-new-post-name';
    var configEl = document.getElementById('mpblog-author-post-config');
    var config = configEl ? JSON.parse(configEl.textContent) : {};
    var pubUrl = config.pubUrl || '';
    var deleteUrl = config.deleteUrl || '';
    var listData = config.posts || {};
    var labels = config.labels || {};

    function byId(id) {
        return document.getElementById(id);
    }

    function setValue(id, value) {
        var el = byId(id);
        if (el) {
            el.value = value === undefined || value === null ? '' : value;
        }
    }

    function decodeEntities(value) {
        var ta = document.createElement('textarea');
        ta.innerHTML = value == null ? '' : value;
        return ta.value;
    }

    function setEditorData(value) {
        if (window.editor) {
            window.editor.setData(value || '');
        }
    }

    function setTree(tree, value) {
        if (tree) {
            tree.updateValue(value || []);
        }
    }

    function setTitle(text) {
        var title = byId('popup_author_post');
        if (title) {
            title.textContent = text;
        }
    }

    function convertDateToDisplay(dateString) {
        if (!dateString) {
            return '';
        }
        var currentDate = new Date(dateString);
        if (isNaN(currentDate.getTime())) {
            return '';
        }
        var month = ('0' + (currentDate.getMonth() + 1)).slice(-2);
        var day = ('0' + currentDate.getDate()).slice(-2);
        var hours = ('0' + currentDate.getHours()).slice(-2);
        var minutes = ('0' + currentDate.getMinutes()).slice(-2);
        return currentDate.getFullYear() + '-' + month + '-' + day + 'T' + hours + ':' + minutes;
    }

    function resetFormModal() {
        setValue('name', '');
        setValue('post_id', '');
        setValue('short_description', '');
        setEditorData('');
        setValue('allow_comment', 0);
        setValue('publish_date', '');
        setTree(window.treeselectCategories, []);
        setTree(window.treeselectTags, []);
        setTree(window.treeselectTopics, []);
        var preview = document.querySelector('#mp_blog_post_form .mp-image-link');
        if (preview) {
            preview.remove();
        }
        var deleteImage = document.querySelector('#mp_blog_post_form .mp-delete-image');
        if (deleteImage) {
            deleteImage.remove();
        }
        var imageInput = byId('image');
        if (imageInput) {
            imageInput.value = '';
        }
        document.querySelectorAll('#mp_blog_post_form .messages').forEach(function (message) {
            message.remove();
        });
    }

    function fillForm(postId, image, keepId) {
        var data = listData[postId];
        setValue('name', data ? decodeEntities(data.name) : '');
        if (keepId) {
            setValue('post_id', data ? data.post_id : '');
        }
        setValue('short_description', data ? decodeEntities(data.short_description) : '');
        setEditorData(data ? data.post_content : '');
        setValue('allow_comment', data ? data.allow_comment : 0);
        setValue('publish_date', convertDateToDisplay(data ? data.publish_date : ''));
        setTree(window.treeselectCategories, data ? data.category_ids : []);
        setTree(window.treeselectTags, data ? data.tag_ids : []);
        setTree(window.treeselectTopics, data ? data.topic_ids : []);
        addPreviewImg(image);
    }

    function addPreviewImg(value) {
        if (!value) {
            return;
        }
        var imageInput = byId('image');
        var holder = imageInput ? imageInput.parentElement : null;
        if (!holder) {
            return;
        }
        var src = pubUrl + 'mageplaza/blog/post/' + value;

        var link = document.createElement('a');
        link.className = 'mp-image-link inline-block';
        link.href = src;
        link.target = '_blank';
        link.rel = 'noopener';

        var img = document.createElement('img');
        img.src = src;
        img.id = 'post_image_image';
        img.title = value;
        img.alt = value;
        img.height = 40;
        img.width = 40;
        img.className = 'h-10 w-10 rounded object-cover';
        link.appendChild(img);
        holder.insertBefore(link, holder.firstChild);

        var wrap = document.createElement('label');
        wrap.className = 'mp-delete-image inline-flex items-center gap-2 text-sm';
        wrap.htmlFor = 'post_image_delete';
        var box = document.createElement('input');
        box.type = 'checkbox';
        box.name = 'image[delete]';
        box.value = '1';
        box.id = 'post_image_delete';
        wrap.appendChild(box);
        wrap.appendChild(document.createTextNode(labels.deleteImg || 'Delete Image'));
        holder.appendChild(wrap);
    }

    function postActionData(event) {
        var el = event && event.currentTarget;
        var button = el && el.closest ? el.closest('[data-post-id]') : null;
        return {
            id: button ? button.getAttribute('data-post-id') : '',
            image: button ? button.getAttribute('data-post-image') || '' : ''
        };
    }

    function handleDelete(event) {
        var data = postActionData(event);
        if (!data.id) {
            return;
        }
        fetch(deleteUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({
                post_id: data.id,
                form_key: hyva.getFormKey()
            })
        })
            .then(function (response) { return response.json(); })
            .then(function (result) {
                if (result.status === 1) {
                    var row = document.querySelector('#mpblog-list-container li[data-post-id="' + result.post_id + '"]');
                    if (row) {
                        row.remove();
                        window.dispatchEvent(new CustomEvent('reload-customer-section-data'));
                    }
                }
            })
            .catch(function (error) { console.error('Error:', error); });
    }

    window.addEventListener('alpine:init', function () {
        window.Alpine.data('mpBlogPostActions', function () {
            return Object.assign(window.hyva.modal({ dialog: DIALOG }), {
                init: function () {
                    var self = this;
                    window.openDialogNewPost = function (event) {
                        self.show(DIALOG, event);
                    };
                    window.hideMyDialogNewPost = function (event) {
                        self.hide(DIALOG, event);
                    };
                },
                openNewPost: function (event) {
                    resetFormModal();
                    setTitle(labels.add || 'Add New Post');
                    this.show(DIALOG, event);
                },
                editPost: function (event) {
                    var data = postActionData(event);
                    resetFormModal();
                    fillForm(data.id, data.image, true);
                    setTitle(labels.edit || 'Edit Post');
                    this.show(DIALOG, event);
                },
                duplicatePost: function (event) {
                    var data = postActionData(event);
                    resetFormModal();
                    fillForm(data.id, data.image, false);
                    setTitle(labels.duplicate || 'Duplicate Post');
                    this.show(DIALOG, event);
                },
                deletePost: handleDelete
            });
        });
    }, { once: true });
})();
