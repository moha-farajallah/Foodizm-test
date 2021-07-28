/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
define([
    'jquery',
    'Intenso_Review/js/intenso-review'
], function ($, intenso) {
    'use strict';

    function processReviews(url, fromPages)
    {
        $('#reviews-loader').show();
        $.ajax({
            url: url,
            cache: true,
            dataType: 'html'
        }).done(function (data) {
            $('#intenso-review-detail').html(data);
            $('#reviews-loader').hide();
            $('.intenso-rating-box').show();
            $('[data-role="product-review"] .pages a').each(function (index, element) {
                $(element).click(function (event) {
                    processReviews($(element).attr('href'), true);
                    event.preventDefault();
                });
            });
            $('[data-role="product-review"] .pager select').change(function (event) {
                processReviews(this.value, true);
                event.preventDefault();
            });
            $('.intenso-rating-histogram a').each(function (index, element) {
                $(element).click(function (event) {
                    processReviews($(element).attr('href'), true);
                    event.preventDefault();
                });
            });
            $('#product-review-container').trigger('contentUpdated');
        }).complete(function () {
            if (fromPages == true) {
                $('html, body').animate({
                    scrollTop: $('#product-review-container').offset().top - 50
                }, 300);
            }
        });
    }

    return function (config, element) {
        if (config.isAjax) {
            processReviews(config.productReviewUrl);
        }
        $(function () {
            $('.product-info-main .reviews-actions a').click(function (event) {
                event.preventDefault();
                var anchor = $(this).attr('href').replace(/^.*?(#|$)/, '');
                $(".product.data.items [data-role='content']").each(function (index) {
                    if (this.id == 'reviews') {
                        if ($('.product.data.items').tabs()) {
                            $('.product.data.items').tabs('activate', index);
                        }
                        $('html, body').animate({
                            scrollTop: $('#' + anchor).offset().top - 50
                        }, 300);
                    }
                });
            });
        });
    };
});
