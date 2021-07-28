/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
define([
    'jquery',
    'Intenso_Review/js/dropzone',
    'Magento_Ui/js/modal/modal',
    'Intenso_Review/js/load-image/load-image',
    'Intenso_Review/js/load-image/load-image-exif',
    'Intenso_Review/js/load-image/load-image-orientation',
    'mage/translate',
    'jquery/hover-intent'
], function ($, Dropzone, modal, loadImage) {
    'use strict';

    $.widget('intenso.review', {
        options: {
            selectors: {
                reviewListId: '#customer-reviews',
                voteBtn: '.helpful-button',
                showComments: '.show-comments',
                addComment: '.add-comment',
                hideComments: '.hide-comments',
                submitComment: 'submit-comment',
                cancelComment: '.cancel-comment',
                reviewFormModal: '#intenso-add-review-modal',
                addReviewLink: '.intenso-add-your-review-link',
                closeButton: '.intenso-close-button',
                addPhotosButton: '.intenso-add-photos-button',
                submitReview: '.intenso-submit-review',
                reviewTextarea: '.review-field-text textarea',
                reviewSummary: '.review-field-summary',
                reviewNickname: '.review-field-nickname',
                reviewEmail: '.review-field-email'
            }
        },

        hoverStatus: false,

        /**
         * Bind events to the appropriate handlers.
         * @private
         */
        _create: function () {
            this._bindVote();
            this._bindShowComments();
            this._bindAddComments();
            this._bindHideComments();
            this._bindOpenReviewModal();
            this._bindPostReview();
            this._bindFormFields();
        },

        _bindVote: function () {
            var self = this,
                url = $(this.options.selectors.reviewListId).data('baseUrl') + 'vote/post';
            $('#product-review-container').on('click', this.options.selectors.voteBtn, function (e) {
                e.preventDefault();
                self.vote(url, $(e.target).data('reviewId'), $(e.target).data('helpful'));
            });
        },

        _bindShowComments: function () {
            var self = this,
                url = $(this.options.selectors.reviewListId).data('baseUrl') + 'comment/listcomment';
            $('#product-review-container').on('click', this.options.selectors.showComments, function (e) {
                e.preventDefault();
                self.showComments(url, $(e.target));
            });
        },

        _bindAddComments: function () {
            var self = this;
            $('#product-review-container').on('click', this.options.selectors.addComment, function (e) {
                e.preventDefault();
                self.addComment($(e.target).data('reviewId'));
            })
        },

        _bindHideComments: function () {
            $('#product-review-container').on('click', this.options.selectors.hideComments, function (e) {
                e.preventDefault();
                $(this).parents('[class^="comments-wrapper-"]').html('');
            });
        },

        _bindOpenReviewModal: function () {
            var self = this,
                dropzoneUploader,
                $form = $('#review-form'),
                url = $(self.options.selectors.reviewFormModal).attr('data-img-post-url');

            $('body').on('click', this.options.selectors.addReviewLink, function (e) {
                e.preventDefault();

                // open modal
                $(self.options.selectors.reviewFormModal).modal('openModal');

                // init Dropzone uploader
                if (url && typeof dropzoneUploader === 'undefined' && $('.dropzone-previews').length) {
                    dropzoneUploader = new Dropzone(document.body, {
                        url: url,
                        previewsContainer: '.dropzone-previews',
                        createImageThumbnails: false,
                        dictDefaultMessage: 'Drop photos here or click to upload.',
                        dictInvalidFileType: 'Allowed formats are: .jpg, .jpeg, .png, .bmp and .gif',
                        dictFileTooBig: 'The file size can\’t exceed 10MB',
                        dictCancelUpload: 'Remove',
                        thumbnailWidth: 120,
                        thumbnailHeight: 120,
                        parallelUploads: 5,
                        maxFilesize: 10,
                        addRemoveLinks: true,
                        maxFiles: 5,
                        acceptedFiles: '.jpg,.jpeg,.png,.gif,.bmp',
                        clickable: self.options.selectors.addPhotosButton
                    });
                    dropzoneUploader.on('maxfilesreached', function () {
                        $(self.options.selectors.addPhotosButton).prop('disabled', true);
                    });
                    dropzoneUploader.on("maxfilesexceeded", function (file) {
                        this.removeFile(file);
                        $(self.options.selectors.addPhotosButton).prop('disabled', true);
                        if (!$('.intenso-modal .modal-footer').find('.intenso-review-maxfilesexceeded').length) {
                            $('<div class="intenso-review-maxfilesexceeded">You can upload a maximum of 5 photos</div>').insertBefore('.intenso-add-photos-button');
                            setTimeout(function () {
                                $('.intenso-review-maxfilesexceeded').fadeOut(1000, function () {
                                    $(this).remove();
                                });
                            }, 5000);
                        }
                    });
                    // post
                    dropzoneUploader.on('sending', function (file, xhr, formData) {
                        formData.append('form_key', $('input[name="form_key"]').val());
                        formData.append('image', file);
                        $(self.options.selectors.submitReview).prop('disabled', true);
                    });
                    // add hidden input with name of the uploaded file
                    dropzoneUploader.on('success', function (file, response) {
                        if (response['success']) {
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'image[]',
                                'data-original-name': response['originalName'],
                                value: response['file']
                            }).appendTo($form);
                        } else {
                            alert(response['message']);
                        }
                        $(self.options.selectors.submitReview).prop('disabled', false);
                    });
                    // remove hidden input when user clicks the delete button and enable upload button
                    dropzoneUploader.on('removedfile', function (file) {
                        $(self.options.selectors.addPhotosButton).prop('disabled', false);
                        $('input[type="hidden"][data-original-name="' + file.name + '"]').remove();
                    });
                    // fix thumbnail orientation
                    dropzoneUploader.on('addedfile', function (file) {
                        var self = this;
                        loadImage.parseMetaData(file, function (data) {
                            // use embedded thumbnail if exists.
                            if (data.exif) {
                                var orientation = data.exif.get('Orientation');
                                if (orientation) {
                                    loadImage(file, function (img) {
                                        self.emit('thumbnail', file, img.toDataURL());
                                    }, { orientation: orientation });
                                    return;
                                }
                            }
                            self.createThumbnail(file);
                        });
                    });
                }
            });
        },

        _bindPostReview: function () {
            var self = this,
                form = $('#review-form');
            $('body').on('click', this.options.selectors.submitReview, function (e) {
                e.preventDefault();
                self.postReview(form);
            });
        },

        _bindFormFields: function () {
            var self = this,
                $body = $('body'),
                reviewSummaryInput = this.options.selectors.reviewSummary + ' input';

            $body.on('keypress input paste', this.options.selectors.reviewTextarea, function (e) {
                if (e.which !== 0 && !$(this).hasClass('intenso-expand-field')) {
                    $(this).addClass('intenso-expand-field');
                    $(self.options.selectors.reviewSummary).fadeIn(500);
                }
                if ($(reviewSummaryInput).val()) {
                    self.showAllFields();
                }
            });
            $body.on('keyup input', this.options.selectors.reviewTextarea, function (e) {
                if (!this.value && $(this).hasClass('intenso-expand-field')) {
                    $(self.options.selectors.reviewEmail).hide(100);
                    $(self.options.selectors.reviewNickname).hide(150);
                    $(self.options.selectors.reviewSummary).hide(200);
                    $(this).removeClass('intenso-expand-field');
                }
            });
            $body.on('keypress input paste', reviewSummaryInput, function (e) {
                self.showAllFields();
            });
        },

        showAllFields: function () {
            $(this.options.selectors.reviewNickname).fadeIn(200);
            $(this.options.selectors.reviewEmail).fadeIn(300);
        },

        _init: function () {
            var self = this;
            $('.summary-popover').hoverIntent({
                over: function () {
                    var productId = $(this).data('review-product'),
                        url = $(this).data('review-url');

                    self.hoverStatus = true;
                    self.showProductReviews(productId, url, $(this).find('.summary-popover-content'));
                },
                out: function () {
                    self.hoverStatus = false;
                    self.hidePopover();
                },
                timeout: 100
            });
            if (typeof this.options.viewMore !== 'undefined' && this.isArray(this.options.viewMore)) {
                for (var i = 0; i < this.options.viewMore.length; ++i) {
                    this.viewMore(this.options.viewMore[i]);
                }
            }
        },

        isArray: function (obj) {
            return obj && obj.constructor == Array;
        },

        viewMore: function (id) {
            var review = $('.review-' + id);
            if (review && review.html()) {
                var words = review.html().split(/[ ]+/);
                if (words.length > this.options.viewMoreLimit) {
                    var counter = 0,
                        toHide = [],
                        toShow = [];
                    for (var i = 0; i < words.length; i++) {
                        if (counter >= this.options.viewMoreLimit) {
                            toHide.push(words[i]);
                        } else {
                            toShow.push(words[i]);
                        }
                        if (words[i].length > 1) {
                            counter++;
                        }
                    }
                    if (counter >= this.options.viewMoreLimit) {
                        var viewMoreLink = $('<a id="view-more-'+id+'" href="#" class="intenso-view-more">'+$.mage.__('Read more')+'</a>');
                        var ellipsis = $('<span class="ellipsis-'+id+'">... </span>');
                        var hide = $('<span></span>');
                        hide.html(toHide.join(' '));
                        hide.addClass('view-more-' + id);
                        hide.hide();
                        review.html(toShow.join(' ') + ellipsis.prop('outerHTML') + viewMoreLink.prop('outerHTML') + ' ' + hide.prop('outerHTML'));
                        $('#view-more-'+id).on('click', function (e) {
                            e.preventDefault();
                            $(this).hide();
                            $('.ellipsis-' + id).hide();
                            $('.view-more-' + id).show();
                        });
                    }
                }
            }
        },

        vote: function (url, reviewId, vote) {
            if (this.voting && typeof url === 'undefined' || typeof reviewId === 'undefined' || typeof vote === 'undefined') {
                return false;
            }
            this.voting = true;
            if ($('.loading-indicator.loader-right.review-' + reviewId).length == 0) {
                $('.voting-buttons-' + reviewId).append('<span class="loading-indicator loader-right review-' + reviewId + '"></span>');
            }
            $.ajax({
                url: url,
                data: {
                    id: reviewId,
                    helpful: vote
                },
                type: 'post',
                dataType: 'json',
                success: function (res) {
                    $('.voting-buttons-' + reviewId).remove();
                    $('.loading-indicator.loader-right.review-' + reviewId).remove();
                    if (res.vote == 'success') {
                        $('.vote-review-id-' + reviewId).find('.alert-inline-success').show();
                        return;
                    } else if (res.vote == 'duplicated') {
                        $('.vote-review-id-' + reviewId).find('.alert-inline-duplicated').show();
                        return;
                    }
                    $('.vote-review-id-' + reviewId).find('.alert-inline-error').show();
                    this.voting = false;
                },
                error: function () {
                    $('.loading-indicator.loader-right.review-' + reviewId).remove();
                    $('.vote-review-id-' + reviewId).find('.alert-inline-error').show();
                    this.voting = false;
                    return;
                }
            });
        },

        postReview: function (form) {
            var self = this,
                url = form.attr('action'),
                modal = $(this.options.selectors.reviewFormModal),
                btnSending = modal.data('btn-sending'),
                btnRetry = modal.data('btn-retry');
            if (form.valid()) {
                $(this.options.selectors.submitReview).text(btnSending).prop('disabled', true);
                $.ajax({
                    url: url,
                    data: form.serialize(),
                    type: 'post',
                    success: function (responseContent) {
                        modal.find('.review-fieldset').hide();
                        $('.dropzone-previews').hide();
                        $(self.options.selectors.addPhotosButton).hide();
                        modal.find('.intenso-review-response').show();
                        $('#intenso-status-message').html(responseContent.message);
                        if (responseContent.success) {
                            // on success
                            modal.find('.sa-error').hide()
                            modal.find('.sa-success').show();
                            modal.find('sa-success').addClass('animate');
                            modal.find('.sa-tip').addClass('animateSuccessTip');
                            modal.find('.sa-long').addClass('animateSuccessLong');
                            $(self.options.selectors.submitReview).hide();
                            $(self.options.selectors.closeButton).show();
                        } else {
                            // on error
                            modal.find('.sa-success').hide();
                            modal.find('.sa-error').show();
                            modal.find('sa-error').addClass('animateErrorIcon');
                            modal.find('.sa-x-mark').addClass('animateXMark');
                            $(self.options.selectors.closeButton).hide();
                            $(self.options.selectors.submitReview).text(btnSending).prop('disabled', false);
                            $(self.options.selectors.submitReview).html('<span>'+btnRetry+'</span>');
                        }
                    },
                    error: function () {
                        // on error
                        modal.find('.review-fieldset').hide();
                        $('.dropzone-previews').hide();
                        $(self.options.selectors.addPhotosButton).hide();
                        modal.find('.intenso-review-response').show();
                        modal.find('.sa-error').show();
                        modal.find('sa-error').addClass('animateErrorIcon');
                        modal.find('.sa-x-mark').addClass('animateXMark');
                        $(self.options.selectors.closeButton).hide();
                        $(self.options.selectors.submitReview).text(btnSending).prop('disabled', false);
                        $(self.options.selectors.submitReview).html('<span>'+btnRetry+'</span>');
                        $('#intenso-status-message').html($.mage.__('We can\'t post your review right now.'));
                    }
                });
            }
        },

        checkFit: function (popover) {
            var position = popover.offset(),
                viewportWidth = $(window).width(),
                popoverWidth = popover.width(),
                margin = viewportWidth * 3/100;

            if (viewportWidth - position.left < popoverWidth + margin) {
                var reference = popover.siblings('.rating-result').offset(),
                    _left = -(popover.width() + reference.left - viewportWidth + (margin/2));
                popover.css('left', _left + 'px');
            }
        },

        showProductReviews: function (productId, url, popover) {
            var self = this;
            if (popover.find('.review-popover').is(':visible')) {
                return false;
            }
            if (this.loadedReviews == undefined) {
                this.loadedReviews = [];
            }
            if (this.loadedUrl == undefined) {
                this.loadedUrl = [];
            }
            self.hidePopover();
            self.checkFit(popover);
            popover.find('.review-popover').fadeIn(300);
            popover.parents('.rating-result').siblings('.summary-popover-corner').addClass('active');

            if (this.loadedReviews[productId]) {
                this.showLoadedReviews(this.loadedReviews[productId], popover);
            } else {
                if (self.loadedUrl[productId]) {
                    return false;
                }
                self.loadedUrl[productId] = true;
                $.ajax({
                    url: url,
                    data: {
                        id: productId
                    },
                    type: 'get',
                    success: function (res) {
                        self.loadedReviews[productId] = res;
                        self.showLoadedReviews(self.loadedReviews[productId], popover);
                    },
                    error: function () {
                        return false;
                    }
                });
            }
        },

        showLoadedReviews: function (summary, popover) {
            var self = this;
            if (!this.hoverStatus) {
                return;
            }
            var el = popover.find('.review-popover');
            if (summary) {
                el.html(summary);
                el.fadeIn(300);
            } else {
                el.html('');
                el.hide();
            }
            setTimeout(function () {
                if (!self.hoverStatus) {
                    self.hidePopover();
                }
            }, 400);
        },

        hidePopover: function (e) {
            $('.review-popover').hide();
            $('.summary-popover-content').css('left', '');
            $('.summary-popover-corner').removeClass('active');
            $('.rating-box.active').removeClass('active');
        },

        showComments: function (url, el) {
            var reviewId = el.data('reviewId');
            if (typeof url === 'undefined' || typeof reviewId === 'undefined') {
                return false;
            }
            if (this.loadedComments == undefined) {
                this.loadedComments = [];
            }
            var that = this;
            if (typeof this.loadedComments[reviewId] != 'undefined') {
                $('.comments-wrapper-' + reviewId).html(that.loadedComments[reviewId]);
                return false;
            }
            if ($('.loading-indicator.loader-left.review-' + reviewId).length == 0) {
                $('.vote-review-id-' + reviewId).prepend('<span class="loading-indicator loader-left review-' + reviewId + '"></span>');
            }
            $.ajax({
                url: url,
                cache: true,
                data: {
                    id: reviewId
                },
                type: 'get',
                success: function (res) {
                    $('.loading-indicator.loader-left.review-' + reviewId).remove();
                    $('.comments-wrapper-' + reviewId).html(res);
                    that.loadedComments[reviewId] = res;
                },
                error: function () {
                    $('.loading-indicator.loader-left.review-' + reviewId).remove();
                    return false;
                }
            });
            return false;
        },

        hideComments: function (reviewId) {
            $('.comments-wrapper-' + reviewId).html('');
        },

        addComment: function (reviewId) {
            var self = this,
                $commentsWrapper = $('.comments-wrapper-' + reviewId),
                form,
                action;
            if (typeof reviewId === 'undefined') {
                return false;
            }
            if ($commentsWrapper.find('form').length === 0) {
                form = $('.intenso-add-comment-form.comment-form-template').clone();
                action = form.find('form').attr('action');
                if (typeof action !== 'undefined') {
                    action = action.replace('__review_id_placeholder__', reviewId);
                    form.find('form').attr({
                        'action': action,
                        'id': 'review_comment_form_'+reviewId
                    });
                    form.find(this.options.selectors.submitComment).attr('data-review-id', reviewId);
                }
                form.removeClass('comment-form-template');
                $('.comments-wrapper-' + reviewId + ' > a').hide();
                if ($commentsWrapper.children().length > 0) {
                    form.insertBefore($('.comments-wrapper-' + reviewId + ' a.add-comment'));
                } else {
                    $commentsWrapper.html(form);
                }
                $commentsWrapper.append('<script type="text/x-magento-init">{ "#review_comment_form_'+reviewId+'": { "validation": {} } }</script>');
                form.find('form').submit(function () {
                    if ($('#review_comment_form_'+reviewId).validation('isValid')) {
                        self.postComment(form.find('form'), reviewId);
                        return false;
                    }
                });
                form.find(this.options.selectors.cancelComment).on('click', function (e) {
                    e.preventDefault();
                    form.remove();
                    $('.comments-wrapper-' + reviewId + ' > a').show();
                });

                $('.review-comment-form').trigger('contentUpdated');
            }
            return false;
        },

        postComment: function (form, reviewId) {
            var url = form.attr('action'),
                button = form.find(':submit'),
                btnText = button.data('btn-txt'),
                btnSending = button.data('btn-sending');
            button.text(btnSending).prop('disabled', true);
            $.ajax({
                url: url,
                data: form.serialize(),
                type: 'post',
                success: function (responseContent) {
                    if (responseContent.success) {
                        $('.comments-wrapper-'+reviewId).html('<span class="intenso-success-msg intenso-checkmark">'+responseContent.message+'</span>');
                    } else {
                        $('.comments-wrapper-'+reviewId+' .intenso-msg-wrapper').html('<span class="intenso-error-msg intenso-error">'+responseContent.message+'</span>');
                    }
                    button.text(btnText).prop('disabled', false);
                },
                error: function () {
                    button.text(btnText).prop('disabled', false);
                    return false;
                }
            });
            return false;
        }
    });

    return $.intenso.review;
});
