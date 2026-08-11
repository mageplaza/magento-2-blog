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
    var configEl = document.getElementById('mpblog-post-view-config');
    var config = configEl ? JSON.parse(configEl.textContent) : {};

    // 1. Helpful-rate (like/dislike) widget -----------------------------------
    if (document.getElementById('mp-blog-review')) {
        (function () {
            var options = {
                url: config.review.url,
                post_id: config.review.postId,
                mode: config.review.mode
            };


            function create() {
                var formKey = hyva.getFormKey();

                var post_id = options.post_id,
                    url = options.url,
                    subPostId = {};
                if (options.mode === '1') {
                    fetch(url, {
                        headers: {
                            'content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
                        },
                        body: 'form_key=' + formKey + '&post_id=' + post_id + '&action=3&mode=' + options.mode,
                        method: 'POST',
                        mode: 'cors',
                        credentials: 'include'
                    })
                        .then(function (response) { return response.json(); })
                        .then(function (response) {
                            if (response.status === 0) {
                                disableReview(response.action);
                            }
                        })
                        .catch(function (error) { console.error('Error:', error); });
                } else {
                    var storedPostData = JSON.parse(getCookie('mpblog_post_data'));
                    if (storedPostData) {
                        subPostId = storedPostData;
                        if (subPostId[post_id] !== undefined) {
                            disableReview(subPostId[post_id].type);
                        }
                    }
                }

                document.querySelectorAll('#mp-blog-review div').forEach(function (el) {
                    el.addEventListener('click', function () {
                        var action = 0,
                            currentPostIds = {},
                            likeId = 0;

                        var storedPostIds = JSON.parse(getCookie('mpblog_post_data'));

                        if (this.classList.contains('mp-blog-like')) {
                            action = 1;
                        }

                        if (storedPostIds && storedPostIds[post_id] !== undefined && options.mode === '0') {
                            likeId = storedPostIds[post_id].likeId;
                            enableReview(storedPostIds[post_id].type);
                            if (action === storedPostIds[post_id].type) {
                                delete storedPostIds[post_id];
                                document.cookie = 'mpblog_post_data=' + JSON.stringify(storedPostIds);
                            } else {
                                storedPostIds[post_id].type = action;
                                disableReview(action);
                            }
                        }

                        fetch(url, {
                            headers: {
                                'content-type': 'application/x-www-form-urlencoded; charset=UTF-8'
                            },
                            body: 'form_key=' + formKey + '&post_id=' + post_id + '&action=' + action + '&mode=' + options.mode + '&likeId=' + likeId,
                            method: 'POST',
                            mode: 'cors',
                            credentials: 'include'
                        })
                            .then(function (response) { return response.json(); })
                            .then(function (response) {
                                var storedPostIds, jsonStringIds;
                                if (response.postLike && options.mode === '0' && typeof currentPostIds[post_id] === 'undefined') {
                                    storedPostIds = receiveCookiePostIds(post_id, action, response.postLike);
                                    jsonStringIds = JSON.stringify(storedPostIds);

                                    document.cookie = 'mpblog_post_data=' + jsonStringIds + '; expires=Sun, 1 Jan 2023 00:00:00 GMT; path=/';
                                }

                                if (response.status) {
                                    if (response.sumLike) {
                                        document.querySelector('#mp-blog-review .mp-blog-like .mp-blog-view').textContent = '(' + response.sumLike + ')';
                                    } else {
                                        document.querySelector('#mp-blog-review .mp-blog-like .mp-blog-view').textContent = '';
                                    }
                                    if (response.sumDislike) {
                                        document.querySelector('#mp-blog-review .mp-blog-dislike .mp-blog-view').textContent = '(' + response.sumDislike + ')';
                                    } else {
                                        document.querySelector('#mp-blog-review .mp-blog-dislike .mp-blog-view').textContent = '';
                                    }
                                }

                                if (response.status) {
                                    enableAllReview();
                                    if (response.postLike) {
                                        disableReview(action);
                                    }
                                }
                                var reloadCustomerDataEvent = new CustomEvent('reload-customer-section-data');
                                window.dispatchEvent(reloadCustomerDataEvent);
                            })
                            .catch(function (error) { console.error('Error:', error); });
                    });
                });
            }

            function disableReview(action) {
                if (action === '1') {
                    document.querySelector('.mp-blog-like').style.backgroundColor = '#658259';
                } else {
                    document.querySelector('.mp-blog-dislike').style.backgroundColor = '#9a6464';
                }
            }

            function enableReview(action) {
                if (action === '1') {
                    document.querySelector('.mp-blog-like').style.backgroundColor = '#6AA84F';
                } else {
                    document.querySelector('.mp-blog-dislike').style.backgroundColor = '#EC3A3C';
                }
            }

            function enableAllReview() {
                document.querySelector('.mp-blog-like').style.backgroundColor = '#6AA84F';
                document.querySelector('.mp-blog-dislike').style.backgroundColor = '#EC3A3C';
            }

            function getCookie(name) {
                var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
                return v ? v[2] : null;
            }

            function receiveCookiePostIds(postId, action, likeId) {
                var postData = {
                        id: postId,
                        type: action,
                        likeId: likeId
                    },
                    receivedJsonStr = getCookie('mpblog_post_data'),
                    postIds = JSON.parse(receivedJsonStr) || {};

                if (postIds[postId] !== undefined) {
                    return postIds;
                }
                postIds[postId] = postData;
                return postIds;
            }

            create();
        })();
    }

    // 2. Disqus loader ---------------------------------------------------------
    if (document.getElementById('disqus_thread')) {
        var disShortName = config.disqusShortName;
        (function () {
            var dsq = document.createElement('script');
            dsq.type = 'text/javascript';
            dsq.async = true;
            dsq.src = '//' + disShortName + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    }

    // 3. Facebook Comments SDK loader --------------------------------------------
    if (document.getElementById('fb-root')) {
        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js = d.createElement(s);
            js.id = id;
            js.src = '//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v12.0&appId=' + config.facebookAppId;
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    }

    // 4 & 5. Related-posts carousel + built-in (Default) comment system ------------
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('card-carousel')) {
            new Splide('#card-carousel', {
                perPage: 3,
                breakpoints: {
                    1028: {
                        perPage: 2
                    }
                }
            }).mount();
        }
    });

    var defaultCmt = document.querySelector('ul.default-cmt__content__cmt-content:first-child');
    if (defaultCmt) {
        (function () {
            var loginUrl = config.comment.loginUrl;
            var like = config.comment.likeText;
            var reply = config.comment.replyText;
            var isLogged = config.comment.isLogged;
            var likedColor = config.comment.likedColor;
            var messengerBox = config.comment.messengerBox;

            var submitCmt = document.querySelector('.default-cmt__content__cmt-block__cmt-box__cmt-btn__btn-submit');
            var likeBtn = defaultCmt.querySelectorAll('.btn-like');
            var replyBtn = defaultCmt.querySelectorAll('.btn-reply');

            var loginBtnCmt = document.querySelector('.default-cmt__cmt-login__btn-login');
            if (loginBtnCmt) {
                loginBtnCmt.addEventListener('click', function () {
                    var socialPopup = document.querySelector('.social-login-popup');
                    if (socialPopup) {
                        openMyDialog();
                        showLogin();
                    } else {
                        window.location.href = loginUrl;
                    }
                });
            }

            // Comment form — canonical Hyvä formValidation, registered via Alpine.data (CSP-safe, no inline eval).
            function initCustomerCommentForm() {
                return Object.assign(
                    hyva.formValidation(document.getElementById('default-cmt__content__cmt-block__guest-form')),
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
                        submitForm() {
                            this.validate()
                                .then(() => {
                                    if (this.errors === 0) {
                                        this.dispatchCommentRequest(document.getElementById('default-cmt__content__cmt-block__guest-form'));
                                    }
                                })
                                .catch((invalid) => {
                                    if (invalid.length > 0) {
                                        invalid[0].focus();
                                    }
                                });
                        },
                        dispatchCommentRequest: function () {
                            callApiSubmitComment();
                        }
                    }
                );
            }
            window.addEventListener('alpine:init', function () {
                window.Alpine.data('initCustomerCommentForm', initCustomerCommentForm);
            }, { once: true });

            function submitComment() {
                if (isLogged === 'Yes' && submitCmt) {
                    submitCmt.addEventListener('click', function () {
                        callApiSubmitComment();
                    });
                }
            }

            submitComment();

            async function callApiSubmitComment() {
                var messagesContainer = document.querySelector('.default-cmt__content__cmt-block__cmt-box .messages');
                if (messagesContainer) {
                    messagesContainer.style.display = 'none';
                }

                var cmtBox = document.querySelector('.default-cmt__content__cmt-block__cmt-box__cmt-input');
                var submitBtn = document.querySelector('.default-cmt__content__cmt-block__cmt-box__cmt-btn__btn-submit');
                var cmtText = cmtBox.value;
                if (cmtText.trim().length) {
                    document.querySelector('.default-cmt_loading').style.display = 'block';
                    submitBtn.disabled = true;
                    await ajaxCommentActions(cmtText, submitBtn);
                    cmtBox.value = '';
                    document.querySelector('.default-cmt_loading').style.display = 'none';
                    submitBtn.disabled = false;
                } else {
                    var cmtInputParent = document.querySelector('.default-cmt__content__cmt-block__cmt-box__cmt-input').parentElement;
                    cmtInputParent.insertAdjacentHTML('beforeend', messengerBox.cmt_warning);
                }
            }

            // ajax call api to comments
            function ajaxCommentActions(cmtText, inputEl, checkReply, cmtId, parentComment) {
                var isReply = (typeof checkReply !== 'undefined') ? 1 : 0,
                    replyId = (typeof cmtId !== 'undefined') ? cmtId : 0,
                    displayReply = (typeof checkReply !== 'undefined');
                var guestName = document.querySelector('#default-cmt__content__cmt-block__guest-box__name-input');
                guestName = guestName ? guestName.value : undefined;

                var guestEmail = document.querySelector('#default-cmt__content__cmt-block__guest-box__email-input');
                guestEmail = guestEmail ? guestEmail.value : undefined;
                var url = window.location.href;
                var body = new URLSearchParams({
                    form_key: hyva.getFormKey(),
                    cmt_text: cmtText,
                    isReply: isReply,
                    replyId: replyId,
                    guestName: guestName,
                    guestEmail: guestEmail
                });
                fetch(url, {
                    headers: {
                        contentType: 'application/x-www-form-urlencoded; charset=UTF-8',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: body,
                    method: 'POST',
                    mode: 'cors',
                    credentials: 'include'
                })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        switch (data.status) {
                            case 'duplicated':
                                document.querySelector('.default-cmt__content__cmt-block__cmt-box__cmt-input').parentElement.appendChild(document.createRange().createContextualFragment(messengerBox.exist_email_warning));
                                break;
                            case 3:
                                document.querySelector('.default-cmt__content__cmt-block').insertAdjacentHTML('afterbegin', messengerBox.comment_approve);
                                break;
                            case 1:
                                displayComment(data, displayReply);
                                var cmtCount = document.querySelectorAll('.default-cmt li').length;
                                if (document.querySelector('.mp-cmt-count')) {
                                    document.querySelector('.mp-cmt-count').textContent = cmtCount;
                                }

                                inputEl.value = '';
                                break;
                            case 'error':
                                if (checkReply !== 'undefined') {
                                    parentComment.insertAdjacentHTML('beforeend', data.error);
                                } else {
                                    document.querySelector('.default-cmt').insertAdjacentHTML('beforeend', data.error);
                                }
                                break;
                        }
                    })
                    .catch(function (error) { console.error('Error:', error); });
            }

            // handle display comments
            function displayComment(cmt, isReply) {
                function htmlComment(text) {
                    var html = '';
                    var sub = text.split('\n');
                    for (var i = 0; i < sub.length; i++) {
                        html += '<p>' + sub[i] + '</p>';
                    }
                    return html;
                }

                var cmtRow = document.createElement('li');
                cmtRow.style.width = '100%';
                cmtRow.id = 'cmt-id-' + cmt.cmt_id;
                cmtRow.className = 'default-cmt__content__cmt-content__cmt-row cmt-row-' + cmt.cmt_id + ' cmt-row col-m-12' +
                    (isReply ? ' reply-row' : '');
                cmtRow.setAttribute('data-cmt-id', cmt.cmt_id);
                if (isReply) {
                    cmtRow.setAttribute('data-reply-id', cmt.reply_cmt);
                }

                cmtRow.innerHTML = '<div class="cmt-row__cmt-username"> ' +
                    '<span class="cmt-row__cmt-username username username__' + cmt.cmt_id + '">' + cmt.user_cmt + '</span> ' +
                    '</div>' +
                    '<div class="cmt-row__cmt-content"> ' +
                    '<p>' + htmlComment(cmt.cmt_text) + '</p> ' +
                    '</div>' +
                    '<div class="cmt-row__cmt-interactions interactions"> <div class="interactions__btn-actions"> ' +
                    '<a class="interactions__btn-actions action btn-like mpblog-like" data-cmt-id="' + cmt.cmt_id + '" click="1">' +
                    '<i class="fa fa-thumbs-up" aria-hidden="true" style="margin-right: 3px"></i>' +
                    '<span class="count-like__like-text"></span>' +
                    '</a>' +
                    '<a class="interactions__btn-actions action btn-reply" data-cmt-id="' + cmt.cmt_id + '">' + reply + '</a> ' +
                    '</div>' +
                    '<div class="interactions__cmt-createdat"> ' +
                    '<span>' + cmt.created_at + '</span> ' +
                    '</div> ';

                if (isReply) {
                    var replyCmtId = cmt.reply_cmt;
                    var replyCmtList = document.querySelectorAll('.default-cmt__content__cmt-content__cmt-row');

                    replyCmtList.forEach(function (cmtEl) {
                        if (cmtEl.getAttribute('data-cmt-id') === replyCmtId) {
                            var replyList = cmtEl.querySelector('ul.default-cmt__content__cmt-content:first-child');

                            if (!replyList) {
                                var newUl = document.createElement('ul');
                                newUl.className = 'default-cmt__content__cmt-content row';
                                newUl.appendChild(cmtRow);
                                cmtEl.appendChild(newUl);

                                likeComment(cmtRow.querySelectorAll('.btn-like'));
                                showReply(cmtRow.querySelectorAll('.btn-reply'));
                            } else {
                                replyList.appendChild(cmtRow);

                                likeComment(cmtRow.querySelectorAll('.btn-like'));
                                showReply(cmtRow.querySelectorAll('.btn-reply'));
                            }

                            return;
                        }
                    });
                } else {
                    defaultCmt.appendChild(cmtRow);

                    likeComment(cmtRow.querySelectorAll('.btn-like'));
                    showReply(cmtRow.querySelectorAll('.btn-reply'));
                }
            }

            // handle click like comment
            function likeComment(btns) {
                btns.forEach(function (likeEl) {
                    likeEl.addEventListener('click', function () {
                        var cmtId = this.getAttribute('data-cmt-id');
                        var cmtRowContainer = this.closest('.default-cmt__content__cmt-content__cmt-row');

                        if (isLogged === 'Yes') {
                            var likeCountText = this.querySelector('span').textContent.trim();
                            var likeCount = parseInt(likeCountText, 10) || 0;
                            if (this.getAttribute('click') === '1') {
                                if (this.classList.contains('mpblog-liked')) {
                                    this.style.color = '#333333';
                                    likeCount--;
                                    this.querySelector('span').textContent = (likeCount === 0) ? '' : likeCount;
                                    this.classList.remove('mpblog-liked');
                                } else {
                                    likeCount++;
                                    this.querySelector('span').textContent = likeCount;
                                    this.style.color = likedColor;
                                    this.classList.add('mpblog-liked');
                                }

                                fetch(window.location.href, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    body: 'cmtId=' + cmtId,
                                    mode: 'cors',
                                    credentials: 'include'
                                })
                                    .then(function (response) { return response.json(); })
                                    .then(function (response) {
                                        if (response.status === 'ok') {
                                            likeEl.setAttribute('click', '1');
                                        } else if (response.status === 'error' && response.hasOwnProperty('error')) {
                                            var errorElement = document.createElement('div');
                                            errorElement.innerHTML = response.error;
                                            defaultCmt.appendChild(errorElement);
                                        }
                                    })
                                    .catch(function (error) { console.error('Error:', error); });
                            }

                            likeEl.setAttribute('click', '0');
                        } else {
                            cmtRowContainer.insertAdjacentHTML('beforeend', messengerBox.login_warning);
                            setTimeout(function () {
                                var errorNotification = document.querySelector('.message.error.message-error');
                                if (errorNotification) {
                                    errorNotification.parentElement.removeChild(errorNotification);
                                }
                            }, 3000);
                        }
                    });
                });
            }

            likeComment(likeBtn);

            // handle show reply comment
            function showReply(btns) {
                btns.forEach(function (btn) {
                    btn.addEventListener('click', function () {
                        var cmtId = (typeof closestWithDataAttr(btn, 'data-cmt-id') !== 'undefined') ? closestWithDataAttr(btn, 'data-cmt-id') : btn.getAttribute('data-cmt-id');

                        var inputCmtID = this.getAttribute('data-cmt-id');
                        var cmtRowCmt = document.querySelector('#cmt-row');
                        var cmtRowContainer = this.closest('.default-cmt__content__cmt-content__cmt-row');

                        if (document.querySelectorAll('.cmt-row__reply-row.row__' + inputCmtID).length) {
                            cmtRowContainer = document.querySelector('#cmt-id-' + cmtId + ' ul:last-child');
                        }

                        var cmtRow = cmtRowContainer.querySelector('.cmt-row__reply-row.row__' + inputCmtID);
                        var cmtName = document.querySelector('.username__' + inputCmtID).textContent;

                        if (isLogged === 'Yes') {
                            if (cmtRowCmt) {
                                cmtRowCmt.style.display = (cmtRowCmt.style.display === 'none') ? 'block' : 'none';
                                cmtRowCmt.parentElement.removeChild(cmtRowCmt);
                            }

                            if (cmtRow) {
                                cmtRow.style.display = (cmtRow.style.display === 'none') ? 'block' : 'none';
                                cmtRow.parentElement.removeChild(cmtRow);
                            } else {
                                cmtRowContainer.insertAdjacentHTML('beforeend', '<div id="cmt-row" class="cmt-row__reply-row row row__' + inputCmtID + ' col-md-12">' +
                                    '<div class="reply-form__form-input form-group col-xs-8 col-md-6">' +
                                    '<label for="reply_cmt' + inputCmtID + '"></label>' +
                                    '<input type="text" id="reply_cmt' + inputCmtID + '" class="form-group__input form-control" placeholder="Press enter to submit reply" autofocus/>' +
                                    '</div>' +
                                    '</div>');

                                var input = document.getElementById('reply_cmt' + inputCmtID);
                                input.value = cmtName + ' ';
                                input.closest('.form-group').appendChild(
                                    document.querySelector('.default-cmt__content__cmt-block__cmt-box__cmt-btn .default-cmt_loading').cloneNode(true)
                                );
                                input.addEventListener('focus', function () {
                                    this.setSelectionRange(1000, 1001);
                                });
                                input.focus();

                                submitReply(input, cmtId, cmtRowContainer);
                            }
                        } else {
                            cmtRowContainer.insertAdjacentHTML('beforeend', messengerBox.login_warning);
                            setTimeout(function () {
                                var errorNotification = document.querySelector('.message.error.message-error');
                                if (errorNotification) {
                                    errorNotification.parentElement.removeChild(errorNotification);
                                }
                            }, 3000);
                        }
                    });
                });
            }

            // handle find comment id closest
            function closestWithDataAttr(element, attribute) {
                while (element && !element.getAttribute(attribute)) {
                    element = element.parentNode;
                }
                return element ? element.getAttribute(attribute) : null;
            }

            showReply(replyBtn);

            var firstCmtRow = document.querySelector('li.default-cmt__content__cmt-content__cmt-row:first-child');
            if (firstCmtRow) {
                firstCmtRow.style.borderTop = 'none';
            }

            // handle reply comment
            function submitReply(input, replyId, parentComment) {
                input.addEventListener('keypress', async function (e) {
                    var text = input.value;
                    if (text !== '') {
                        if (e.keyCode === 13) {
                            input.nextElementSibling.style.display = 'block';
                            input.disabled = true;
                            await ajaxCommentActions(text, input, true, replyId, parentComment);
                            input.closest('.cmt-row__reply-row').style.display = 'none';
                            input.nextElementSibling.style.display = 'none';
                            input.disabled = false;
                            var cmtRowEl = document.getElementById('cmt-row');
                            if (cmtRowEl) {
                                cmtRowEl.remove();
                            }
                        }
                    }
                });
            }
        })();
    }
})();
