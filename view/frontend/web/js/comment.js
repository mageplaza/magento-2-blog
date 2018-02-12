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
 * @copyright   Copyright (c) 2018 Mageplaza (http://www.mageplaza.com/)
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
                $('.default-cmt_loading').show();
                $(this).prop('disabled', true);
                var ajaxRequest = ajaxCommentActions(cmtText, submitCmt);
                ajaxRequest.done(function () {
                    cmtBox.val('');
                    $('.default-cmt_loading').hide();
                    $(this).prop('disabled', false);
                }.bind(this));
            }
        });
    }

    //like action
    function likeComment(btn) {
        btn.each(function () {
            var likeEl = $(this);
            likeEl.click(function () {
                var cmtId = $(this).attr('data-cmt-id'),
                    cmtRowContainer = $(this).closest('.default-cmt__content__cmt-content__cmt-row');
                if (isLogged === 'Yes') {
                    var likeCount = $(this).find('span').text();
                    if ($(this).attr('click') === '1') {
                        if ($(this).hasClass('mpblog-liked')) {
                            $(this).css('color', '#333333');
                            likeCount--;
                            $(this).find('span').text((likeCount === 0) ? "" : likeCount);
                            $(this).removeClass('mpblog-liked')

                        } else {
                            likeCount++;
                            $(this).find('span').text(likeCount);
                            $(this).css('color', likedColor);
                            $(this).addClass('mpblog-liked')
                        }
                        $.ajax({
                            type: "POST",
                            url: window.location.href,
                            data: {cmtId: cmtId},
                            success: function (response) {
                                if (response.status === 'ok') {
                                    $(likeEl).attr('click', '1');
                                } else if (response.status === 'error' && response.hasOwnProperty(error)) {
                                    defaultCmt.append(response.error);
                                }
                            }
                        });
                    }
                    $(this).attr('click', '0');

                } else {
                    cmtRowContainer.append(loginWarning);
                    jQuery.fn.fadeOutAndRemove = function (speed) {
                        $(this).fadeOut(speed, function () {
                            $(this).remove();
                        })
                    };
                    var removeNotification = function () {
                        $('.message.error.message-error.row').fadeOutAndRemove('normal');
                    };
                    setTimeout(removeNotification, 3000);
                }

            });
        });
    }

    //show reply
    function showReply(btn) {
        btn.each(function () {

            $(this).click(function () {
                var cmtId = $(this).attr('data-cmt-id'),
                    cmtRowContainer = $(this).closest('.default-cmt__content__cmt-content__cmt-row'),
                    cmtRow = cmtRowContainer.find('.cmt-row__reply-row');

                if (isLogged === 'Yes') {
                    if (cmtRow.length) {
                        cmtRow.toggle();
                    } else {
                        cmtRowContainer.append('<div class="cmt-row__reply-row row">' +
                            '<div class="reply-form__form-input form-group col-xs-8 col-md-6">' +
                            '<label for="reply_cmt' + cmtId + '"></label>' +
                            '<input type="text" id="reply_cmt' + cmtId + '" class="form-group__input form-control" placeholder="Press enter to submit reply" autofocus />' +
                            '</div>' +
                            '</div>');
                        var input = $('#reply_cmt' + cmtId);
                        input.closest('.form-group').append(
                            $('.default-cmt__content__cmt-block__cmt-box__cmt-btn .default-cmt_loading').clone()
                        );
                        input.focus();
                        submitReply(input, cmtId, cmtRowContainer);
                    }
                } else {
                    cmtRowContainer.append(loginWarning);
                    jQuery.fn.fadeOutAndRemove = function (speed) {
                        $(this).fadeOut(speed, function () {
                            $(this).remove();
                        })
                    };
                    var removeNotification = function () {
                        $('.message.error.message-error.row').fadeOutAndRemove('normal');
                    };
                    setTimeout(removeNotification, 3000);
                }
            });
        });
    }

    //submit reply
    function submitReply(input, replyId, parentComment) {
        input.keypress(function (e) {
            var text = input.val();
            if (text !== '') {
                if (e.keyCode === 13) {
                    input.siblings('.default-cmt_loading').show();
                    input.prop('disabled', true);

                    var ajaxRequest = ajaxCommentActions(text, input, true, replyId, parentComment);
                    ajaxRequest.done(function () {
                        input.closest('.cmt-row__reply-row').hide();
                        input.siblings('.default-cmt_loading').hide();
                        input.prop('disabled', false);
                    });
                }
            }
        });
    }

    //submit comment actions
    function ajaxCommentActions(cmtText, inputEl, checkReply, cmtId, parentComment) {
        var isReply = (typeof checkReply !== 'undefined') ? 1 : 0,
            replyId = (typeof cmtId !== 'undefined') ? cmtId : 0,
            displayReply = (typeof checkReply !== 'undefined');

        return $.ajax({
            type: 'POST',
            url: window.location.href,
            data: {cmt_text: cmtText, isReply: isReply, replyId: replyId},
            success: function (response) {
                if (parseInt(response.status) === 3) {
                    $('.default-cmt__content__cmt-block').prepend('<div class="message success message-success row">Your comment has been sent successfully. Please wait admin approve !</div>')
                } else if (parseInt(response.status) === 1) {
                    displayComment(response, displayReply);
                    inputEl.val('');
                } else if (response.status === 'error' && response.hasOwnProperty(error)) {
                    if (checkReply !== 'undefined') {
                        parentComment.append(response.error);
                    } else {
                        defaultCmt.append(response.error);
                    }
                }
            }
        });
    }

    // display comment
    function displayComment(cmt, isReply) {
        var cmtRow = '<li id="cmt-id-' + cmt.cmt_id + '" class="default-cmt__content__cmt-content__cmt-row cmt-row col-xs-12 ' + (isReply ? ('reply-row') : '') + '" data-cmt-id="' + cmt.cmt_id + '"' + (isReply ? ('data-reply-id="' + cmt.reply_cmt + '"') : '') + '> <div class="cmt-row__cmt-username"> <span class="cmt-row__cmt-username username">' + cmt.user_cmt + '</span> </div> <div class="cmt-row__cmt-content"> <p>' + cmt.cmt_text + '</p> </div> <div class="cmt-row__cmt-interactions interactions"> <div class="interactions__btn-actions"> <a class="interactions__btn-actions action btn-like mpblog-like" data-cmt-id="' + cmt.cmt_id + '" click="1"><i class="fa fa-thumbs-up" aria-hidden="true" style="margin-right: 3px"></i><span class="count-like__like-text"></span></a> <a class="interactions__btn-actions action btn-reply" data-cmt-id="' + cmt.cmt_id + '">' + reply + '</a>  </div> <div class="interactions__cmt-createdat"> <span>' + cmt.created_at + '</span> </div> </div> </li>';
        if (isReply) {
            var replyCmtId = cmt.reply_cmt;
            var replyCmt = defaultCmt.find('.default-cmt__content__cmt-content__cmt-row');
            replyCmt.each(function () {
                var cmtEl = $(this);
                if (cmtEl.attr('data-cmt-id') === replyCmtId) {
                    var replyList = cmtEl.find('ul.default-cmt__content__cmt-content:first');
                    if (!replyList.length) {
                        cmtRow = $('<ul class="default-cmt__content__cmt-content row">' + cmtRow + '</ul>');
                        cmtEl.append(cmtRow);

                        likeComment(cmtRow.find('.btn-like'));
                        showReply(cmtRow.find('.btn-reply'));
                    } else {
                        cmtRow = $(cmtRow);
                        replyList.append(cmtRow);

                        likeComment(cmtRow.find('.btn-like'));
                        showReply(cmtRow.find('.btn-reply'));
                    }

                    return false;
                }
            });
        } else {
            cmtRow = $(cmtRow);
            defaultCmt.append(cmtRow);

            likeComment(cmtRow.find('.btn-like'));
            showReply(cmtRow.find('.btn-reply'));
        }
    }
});