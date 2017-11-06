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
 * @copyright   Copyright (c) 2017 Mageplaza (http://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */


require([
    'jquery'
], function ($) {
    var cmtBox = $('.default-cmt__content__cmt-block__cmt-box__cmt-input'),
        submitCmt = $('.default-cmt__content__cmt-block__cmt-box__cmt-btn__btn-submit'),
        defaultCmt = $('ul.default-cmt__content__cmt-content:first'),
        likeBtn = defaultCmt.find('.btn-like'),
        replyBtn = defaultCmt.find('.btn-reply');

    submitComment();
    likeComment(likeBtn);
    showReply(replyBtn);

    $('li.default-cmt__content__cmt-content__cmt-row:first').css({'border-top': 'none'});
    $('.default-cmt__cmt-login__btn-login').click(function () {
        var socialPopup = $("[href$='social-login-popup']");
        if (socialPopup.length) {
            socialPopup.first().trigger("click");
        } else {
            window.location.href = loginUrl;
        }
    });

    //submit comment
    function submitComment() {
        submitCmt.click(function () {
            var cmtText = cmtBox.val();
            if (cmtText.trim().length) {
                ajaxCommentActions(cmtText, submitCmt);
            }
        });
    }

    // display comment
    function displayComment(cmt, isReply) {
        var cmtRow = '<li class="default-cmt__content__cmt-content__cmt-row cmt-row col-xs-12 ' + (isReply ? ('reply-row') : '') + '" data-cmt-id="' + cmt.cmt_id + '"' + (isReply ? ('data-reply-id="' + cmt.reply_cmt + '"') : '') + '> <div class="cmt-row__cmt-username"> <span class="cmt-row__cmt-username username">' + cmt.user_cmt + '</span> </div> <div class="cmt-row__cmt-content"> <p>' + cmt.cmt_text + '</p> </div> <div class="cmt-row__cmt-interactions interactions"> <div class="interactions__btn-actions"> <a class="interactions__btn-actions action btn-like-new" data-cmt-id="' + cmt.cmt_id + '">' + like + '</a> <a class="interactions__btn-actions action btn-reply-new" data-cmt-id="' + cmt.cmt_id + '">' + reply + '</a> <a class="interactions__btn-actions count-like" ><i class="fa fa-thumbs-up" aria-hidden="true"></i> <span class="count-like__like-text"></span></a> </div> <div class="interactions__cmt-createdat"> <span>' + cmt.created_at + '</span> </div> </div> </li>';
        if (isReply) {
            var replyCmtId = cmt.reply_cmt;
            var replyCmt = defaultCmt.find('.default-cmt__content__cmt-content__cmt-row');
            replyCmt.each(function () {
                var cmtEl = $(this);
                if (cmtEl.attr('data-cmt-id') === replyCmtId) {
                    var replyList = cmtEl.find('ul.default-cmt__content__cmt-content:first');
                    if (!replyList.length) {
                        cmtRow = '<ul class="default-cmt__content__cmt-content row">' + cmtRow + '</ul>';
                        cmtEl.append(cmtRow);
                    } else {
                        replyList.append(cmtRow);
                    }
                }
            });
        } else {
            defaultCmt.append(cmtRow);
        }
    }

    //like action
    function likeComment(btn) {
        btn.each(function () {
            var likeEl = $(this);
            likeEl.click(function () {
                var cmtId = $(this).attr('data-cmt-id');

                $.ajax({
                    type: "POST",
                    url: window.location.href,
                    data: {cmtId: cmtId},
                    success: function (response) {
                        if (response.status === 'ok') {
                            likeEl.parent().find('.count-like__like-text').text(response.count_like);
                        } else if (response.status === 'error' && response.hasOwnProperty(error)) {
                            defaultCmt.append(response.error);
                        }
                    }
                });
            });
        });
    }

    //show reply
    function showReply(btn) {
        btn.each(function () {
            var replyEl = $(this);
            replyEl.click(function () {
                defaultCmt.find('.cmt-row__reply-row').hide();
                var cmtId = $(this).attr('data-cmt-id');
                var cmtRow = defaultCmt.find('.default-cmt__content__cmt-content__cmt-row');
                cmtRow.each(function () {
                    var cmtEl = $(this);
                    if (cmtEl.attr('data-cmt-id') === cmtId) {
                        cmtEl.find('.cmt-row__reply-row').remove();
                        cmtEl.append('<div class="cmt-row__reply-row row"><div class="reply-form__form-input form-group col-xs-8 col-md-6"><label for="reply_cmt' + cmtId + '"></label><input type="text" id="reply_cmt' + cmtId + '" class="form-group__input form-control" autofocus /></div></div>');
                        var input = $('#reply_cmt' + cmtId)
                        submitReply(input, cmtId, cmtEl);
                    }
                });
            });
        });
    }

    //submit reply
    function submitReply(input, replyId, parentComment) {
        input.keypress(function (e) {
            var text = input.val();
            if (text !== '') {
                if (e.keyCode === 13) {
                    ajaxCommentActions(text, input, true, replyId, parentComment);
                }
            }
        });
    }

    //submit comment actions
    function ajaxCommentActions(cmtText, inputEl, checkReply, cmtId, parentComment) {
        var isReply = (typeof checkReply !== 'undefined') ? 1 : 0;
        var replyId = (typeof cmtId !== 'undefined') ? cmtId : 0;
        var displayReply = (typeof checkReply !== 'undefined');

        $.ajax({
            type: 'POST',
            url: window.location.href,
            data: {cmt_text: cmtText, isReply: isReply, replyId: replyId},
            success: function (response) {
                if (response.status === 'ok') {
                    displayComment(response, displayReply);
                    inputEl.val('');
                } else if (response.status === 'error' && response.hasOwnProperty(error)) {
                    if (checkReply !== 'undefined') {
                        parentComment.append(response.error);
                    } else {
                        defaultCmt.append(response.error);
                    }
                }

                var likeBtnNew = defaultCmt.find('.btn-like-new'),
                    replyNew = defaultCmt.find('.btn-reply-new');
                likeComment(likeBtnNew);
                showReply(replyNew);
            }
        });
    }
});