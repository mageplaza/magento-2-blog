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
    if (!configEl || configEl.dataset.bound === '1') {
        return;
    }
    configEl.dataset.bound = '1';

    var config = {};
    try {
        config = configEl ? JSON.parse(configEl.textContent) : {};
    } catch (e) {
        config = {};
    }

    function onReady (fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, { once: true });
        } else {
            fn();
        }
    }

    function getCookie (name) {
        var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
        return v ? v[2] : null;
    }

    function readVoteCookie () {
        try {
            return JSON.parse(getCookie('mpblog_post_data'));
        } catch (e) {
            return null;
        }
    }

    function writeVoteCookie (data) {
        var expires = new Date(Date.now() + 365 * 24 * 60 * 60 * 1000).toUTCString();
        document.cookie = 'mpblog_post_data=' + JSON.stringify(data) + '; expires=' + expires + '; path=/; SameSite=Lax';
    }

    function initReview () {
        var widget = document.getElementById('mp-blog-review');
        if (!widget || !config.review) {
            return;
        }

        var url = config.review.url,
            postId = config.review.postId,
            mode = config.review.mode,
            formKey = hyva.getFormKey(),
            likeEl = widget.querySelector('.mp-blog-like'),
            dislikeEl = widget.querySelector('.mp-blog-dislike');

        function buttonFor (action) {
            return (action === '1' || action === 1) ? likeEl : dislikeEl;
        }

        function setActive (el, active) {
            if (!el) {
                return;
            }
            el.classList.toggle('is-active', active);
            el.setAttribute('aria-pressed', active ? 'true' : 'false');
        }

        function disableReview (action) {
            setActive(buttonFor(action), true);
        }

        function enableReview (action) {
            setActive(buttonFor(action), false);
        }

        function enableAllReview () {
            setActive(likeEl, false);
            setActive(dislikeEl, false);
        }

        function setCounter (el, value) {
            var counter = el ? el.querySelector('.mp-blog-view') : null;
            if (counter) {
                counter.textContent = value ? '(' + value + ')' : '';
            }
        }

        function storeVote (action, likeId) {
            var postIds = readVoteCookie() || {};
            if (likeId) {
                postIds[postId] = { id: postId, type: action, likeId: likeId };
            } else {
                delete postIds[postId];
            }
            writeVoteCookie(postIds);
        }

        function restoreState () {
            if (mode === '1') {
                fetch(url, {
                    headers: { 'content-type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: 'form_key=' + formKey + '&post_id=' + postId + '&action=3&mode=' + mode,
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
                var stored = readVoteCookie();
                if (stored && stored[postId] !== undefined) {
                    disableReview(stored[postId].type);
                }
            }
        }

        function vote (button) {
            var action = button.classList.contains('mp-blog-like') ? 1 : 0,
                likeId = 0,
                stored = readVoteCookie();

            if (stored && stored[postId] !== undefined && mode === '0') {
                likeId = stored[postId].likeId;
                enableReview(stored[postId].type);
                if (action !== stored[postId].type) {
                    disableReview(action);
                }
            }

            fetch(url, {
                headers: { 'content-type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: 'form_key=' + formKey + '&post_id=' + postId + '&action=' + action + '&mode=' + mode + '&likeId=' + likeId,
                method: 'POST',
                mode: 'cors',
                credentials: 'include'
            })
            .then(function (response) { return response.json(); })
            .then(function (response) {
                if (response['status']) {
                    if (mode === '0') {
                        storeVote(action, response['postLike']);
                    }
                    setCounter(likeEl, response.sumLike);
                    setCounter(dislikeEl, response.sumDislike);
                    enableAllReview();
                    if (response['postLike']) {
                        disableReview(action);
                    }
                } else {
                    enableAllReview();
                    restoreState();
                }
                window.dispatchEvent(new CustomEvent('reload-customer-section-data'));
            })
            .catch(function (error) { console.error('Error:', error); });
        }

        [likeEl, dislikeEl].forEach(function (button) {
            if (!button) {
                return;
            }
            button.addEventListener('click', function () {
                vote(button);
            });
            button.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    button.click();
                }
            });
        });

        restoreState();
    }

    onReady(initReview);

    if (document.getElementById('disqus_thread')) {
        var disShortName = config.disqusShortName;
        (function () {
            var dsq   = document.createElement('script');
            dsq.type  = 'text/javascript';
            dsq.async = true;
            dsq.src   = '//' + disShortName + '.disqus.com/embed.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
        })();
    }

    if (document.getElementById('fb-root')) {
        (function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {
                return;
            }
            js     = d.createElement(s);
            js.id  = id;
            js.src = '//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v12.0&appId=' + config.facebookAppId;
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));
    }

    var defaultCmt = document.querySelector('ul.default-cmt__content__cmt-content:first-child');
    if (defaultCmt && config.comment) {
        var loginUrl     = config.comment.loginUrl,
            reply        = config.comment.replyText,
            isLogged     = config.comment.isLogged,
            likedColor   = config.comment.likedColor,
            messengerBox = config.comment.messengerBox;

        var loginBtnCmt = document.querySelector('.default-cmt__cmt-login__btn-login');
        if (loginBtnCmt) {
            loginBtnCmt.addEventListener('click', function () {
                if (document.querySelector('.social-login-popup') && typeof openMyDialog === 'function' && typeof showLogin === 'function') {
                    openMyDialog();
                    showLogin();
                } else {
                    window.location.href = loginUrl;
                }
            });
        }

        var initBlogCommentForm = function () {
            return Object.assign(
                hyva.formValidation(document.getElementById('blog-comment-form')),
                {
                    submitForm () {
                        this.validate()
                            .then(async () => {
                                var cmtBox    = document.querySelector('.default-cmt__content__cmt-block__cmt-box__cmt-input');
                                var submitCmt = document.querySelector('.default-cmt__content__cmt-block__cmt-box__cmt-btn__btn-submit');
                                var loader    = document.querySelector('.default-cmt_loading');
                                var cmtText   = cmtBox ? cmtBox.value : '';
                                if (!cmtText.trim().length) {
                                    return;
                                }
                                if (loader) { loader.classList.remove('hidden'); }
                                if (submitCmt) { submitCmt.disabled = true; }
                                await ajaxCommentActions(cmtText, submitCmt);
                                cmtBox.value = '';
                                if (loader) { loader.classList.add('hidden'); }
                                if (submitCmt) { submitCmt.disabled = false; }
                            })
                            .catch((invalid) => {
                                if (invalid && invalid.length > 0) {
                                    invalid[0].focus();
                                }
                            });
                    }
                }
            );
        };
        window.addEventListener('alpine:init', () => Alpine.data('initBlogCommentForm', initBlogCommentForm), { once: true });

        var ajaxCommentActions = function (cmtText, inputEl, checkReply, cmtId, parentComment) {
            var displayReply = (typeof checkReply !== 'undefined'),
                isReply      = displayReply ? 1 : 0,
                replyId      = (typeof cmtId !== 'undefined') ? cmtId : 0;
            var guestName  = document.querySelector('#default-cmt__content__cmt-block__guest-box__name-input')?.value;
            var guestEmail = document.querySelector('#default-cmt__content__cmt-block__guest-box__email-input')?.value;
            const body     = new URLSearchParams({
                form_key: hyva.getFormKey(),
                cmt_text: cmtText,
                isReply: isReply,
                replyId: replyId,
                guestName: guestName,
                guestEmail: guestEmail
            });
            return fetch(window.location.href, {
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: body,
                method: 'POST',
                mode: 'cors',
                credentials: 'include'
            })
            .then(response => response.json())
            .then(data => {
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
                        if (displayReply && parentComment) {
                            parentComment.insertAdjacentHTML('beforeend', data.error);
                        } else {
                            document.querySelector('.default-cmt').insertAdjacentHTML('beforeend', data.error);
                        }
                        break;
                }
            })
            .catch(error => console.error('Error:', error));
        };

        var displayComment = function (cmt, isReply) {
            function htmlComment (text) {
                var html = '';
                var sub  = text.split('\n');
                for (var i = 0; i < sub.length; i++) {
                    html += '<p>' + sub[i] + '</p>';
                }
                return html;
            }

            var cmtRow       = document.createElement('li');
            cmtRow.id        = 'cmt-id-' + cmt.cmt_id;
            cmtRow.className = 'default-cmt__content__cmt-content__cmt-row cmt-row-' + cmt.cmt_id + ' cmt-row' +
                (isReply ? ' reply-row' : '');
            cmtRow.setAttribute('data-cmt-id', cmt.cmt_id);
            if (isReply) {
                cmtRow.setAttribute('data-reply-id', cmt.reply_cmt);
            }

            cmtRow.innerHTML = '<div class="cmt-row__cmt-username"> ' +
                '<span class="cmt-row__cmt-username username username__' + cmt.cmt_id + '">' + cmt.user_cmt + '</span> ' +
                '</div>' +
                '<div class="cmt-row__cmt-content"> ' +
                htmlComment(cmt.cmt_text) + ' ' +
                '</div>' +
                '<div class="cmt-row__cmt-interactions interactions"> <div class="interactions__btn-actions"> ' +
                '<a class="interactions__btn-actions action btn-like mpblog-like" data-cmt-id="' + cmt.cmt_id + '" click="1">' +
                '<svg class="inline-block align-middle mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" width="16" height="16" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>' +
                '<span class="count-like__like-text"></span>' +
                '</a>' +
                '<a class="interactions__btn-actions action btn-reply" data-cmt-id="' + cmt.cmt_id + '">' + reply + '</a> ' +
                '</div>' +
                '<div class="interactions__cmt-createdat"> ' +
                '<span>' + cmt.created_at + '</span> ' +
                '</div> ';

            if (isReply) {
                var replyCmtId = String(cmt.reply_cmt);
                document.querySelectorAll('.default-cmt__content__cmt-content__cmt-row').forEach(function (cmtEl) {
                    if (String(cmtEl.getAttribute('data-cmt-id')) !== replyCmtId || cmtRow.parentNode) {
                        return;
                    }
                    var replyList = cmtEl.querySelector('ul.default-cmt__content__cmt-content:first-child');
                    if (!replyList) {
                        replyList = document.createElement('ul');
                        replyList.className = 'default-cmt__content__cmt-content row';
                        cmtEl.appendChild(replyList);
                    }
                    replyList.appendChild(cmtRow);
                });
            } else {
                defaultCmt.appendChild(cmtRow);
            }

            likeComment(cmtRow.querySelectorAll('.btn-like'));
            showReply(cmtRow.querySelectorAll('.btn-reply'));
        };

        var showLoginWarning = function (container) {
            container.insertAdjacentHTML('beforeend', messengerBox.login_warning);
            setTimeout(function () {
                var errorNotification = document.querySelector('.message.error.message-error');
                if (errorNotification) {
                    errorNotification.parentElement.removeChild(errorNotification);
                }
            }, 3000);
        };

        var likeComment = function (btns) {
            btns.forEach(function (likeEl) {
                likeEl.addEventListener('click', function () {
                    var cmtId           = this.getAttribute('data-cmt-id');
                    var cmtRowContainer = this.closest('.default-cmt__content__cmt-content__cmt-row');

                    if (isLogged !== 'Yes') {
                        showLoginWarning(cmtRowContainer);
                        return;
                    }

                    var likeCountText = this.querySelector('span').textContent.trim();
                    var likeCount     = parseInt(likeCountText, 10) || 0;
                    if (this.getAttribute('click') === '1') {
                        if (this.classList.contains('mpblog-liked')) {
                            this.style.color = '';
                            likeCount--;
                            this.querySelector('span').textContent = (likeCount === 0) ? '' : likeCount;
                            this.classList.remove('mpblog-liked');
                        } else {
                            likeCount++;
                            this.querySelector('span').textContent = likeCount;
                            this.style.color                       = likedColor;
                            this.classList.add('mpblog-liked');
                        }

                        fetch(window.location.href, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: 'form_key=' + hyva.getFormKey() + '&cmtId=' + cmtId,
                            mode: 'cors',
                            credentials: 'include'
                        })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status === 'ok') {
                                likeEl.setAttribute('click', '1');
                            } else if (response.status === 'error' && response.hasOwnProperty('error')) {
                                var errorElement       = document.createElement('div');
                                errorElement.innerHTML = response.error;
                                defaultCmt.appendChild(errorElement);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }

                    likeEl.setAttribute('click', '0');
                });
            });
        };

        var closestWithDataAttr = function (element, attribute) {
            while (element && !element.getAttribute(attribute)) {
                element = element.parentNode;
            }
            return element ? element.getAttribute(attribute) : null;
        };

        var submitReply = function (input, replyId, parentComment) {
            input.addEventListener('keypress', async function (e) {
                var text = input.value;
                if (text !== '' && e.key === 'Enter') {
                    var loader = input.nextElementSibling;
                    if (loader) { loader.classList.remove('hidden'); }
                    input.disabled = true;
                    await ajaxCommentActions(text, input, true, replyId, parentComment);
                    var replyRow = input.closest('.cmt-row__reply-row');
                    if (replyRow) {
                        replyRow.remove();
                    }
                }
            });
        };

        var showReply = function (btns) {
            btns.forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var cmtId           = closestWithDataAttr(btn, 'data-cmt-id') || btn.getAttribute('data-cmt-id');
                    var inputCmtID      = this.getAttribute('data-cmt-id');
                    var cmtRowCmt       = document.querySelector('#cmt-row');
                    var cmtRowContainer = this.closest('.default-cmt__content__cmt-content__cmt-row');

                    if (isLogged !== 'Yes') {
                        showLoginWarning(cmtRowContainer);
                        return;
                    }

                    if (document.querySelectorAll('.cmt-row__reply-row.row__' + inputCmtID).length) {
                        cmtRowContainer = document.querySelector('#cmt-id-' + cmtId + ' ul:last-child');
                    }

                    var cmtRow  = cmtRowContainer.querySelector('.cmt-row__reply-row.row__' + inputCmtID);
                    var cmtName = document.querySelector('.username__' + inputCmtID).textContent;

                    if (cmtRowCmt) {
                        cmtRowCmt.parentElement.removeChild(cmtRowCmt);
                    }

                    if (cmtRow) {
                        if (cmtRow.parentElement) {
                            cmtRow.parentElement.removeChild(cmtRow);
                        }
                        return;
                    }

                    cmtRowContainer.insertAdjacentHTML('beforeend', '<div id="cmt-row" class="cmt-row__reply-row row__' + inputCmtID + '">' +
                        '<div class="flex items-center gap-2">' +
                        '<label class="sr-only" for="reply_cmt' + inputCmtID + '">' + reply + '</label>' +
                        '<input type="text" id="reply_cmt' + inputCmtID + '" placeholder="Press enter to submit reply"/>' +
                        '</div>' +
                        '</div>');

                    var input = document.getElementById('reply_cmt' + inputCmtID);
                    input.value = cmtName + ' ';
                    var loaderTemplate = document.querySelector('.default-cmt__content__cmt-block__cmt-box__cmt-btn .default-cmt_loading');
                    if (loaderTemplate) {
                        input.parentElement.appendChild(loaderTemplate.cloneNode(true));
                    }
                    input.focus();

                    submitReply(input, cmtId, cmtRowContainer);
                });
            });
        };

        likeComment(defaultCmt.querySelectorAll('.btn-like'));
        showReply(defaultCmt.querySelectorAll('.btn-reply'));
    }
})();
