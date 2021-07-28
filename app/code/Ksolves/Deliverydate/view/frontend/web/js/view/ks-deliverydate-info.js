/**
 * Ksolves
 *
 * @category   Ksolves
 * @package    Ksolves_Deliverydate
 * @author     Ksolves Team
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define(
    [
        'jquery',
        'ko',
        'underscore',
        'uiComponent',
        'Ksolves_Deliverydate/js/model/ksdd-data',
        'Ksolves_Deliverydate/js/model/ks-info',
        'jquery/ui',
        'jquery/jquery-ui-timepicker-addon'
    ],
    function ($, ko, _, Component, ksDdData, ksDeliverydateInfo) {
        'use strict';

        var cacheKeyDeliveryDate    = 'ksDeliveryDate',
            cacheKeyDeliveryTime    = 'ksDeliverydateTimeSlot',
            cacheKeyDeliveryComment = 'ksDeliverydateComment',

            deliveryDaysOff    = window.checkoutConfig.ksDeliverydateConfig.deliveryDaysOff || [],
            deliveryDateFormat = window.checkoutConfig.ksDeliverydateConfig.deliveryDateFormat,
            deliveryDateOff    = [];

        function ksPrepareSubValue(object, cacheKey) {
            object(ksDdData.getData(cacheKey));
            object.subscribe(function (newValue) {
                ksDdData.setData(cacheKey, newValue);
            });
        }

        return Component.extend({
            defaults: {
                template: 'Ksolves_Deliverydate/container/form/ks-deliverydate-info'
            },
            ksDeliveryDate: ksDeliverydateInfo().ksDeliveryDate,
            ksDeliverydateTimeSlot: ksDeliverydateInfo().ksDeliverydateTimeSlot,
            ksDeliverydateComment: ksDeliverydateInfo().ksDeliverydateComment,
            isVisible: ko.observable(ksDdData.getData(cacheKeyDeliveryDate)),

            initialize: function () {
                this._super();

                var self = this;
				var sameDay = new Date();

                var todayDate = window.checkoutConfig.ksDeliverydateConfig.getToadyDate;
                if (todayDate !== 1) {
                    var today = new Date();
                    sameDay = new Date();
                    sameDay.setDate(today.getDate() + 1);
                }
                var isAvailableDate = window.checkoutConfig.ksDeliverydateConfig.deliveryDateOff;
                

                ko.bindingHandlers.ks_deliverydatepicker = {
                    init: function (element) {
                        var ks_options = {
                            minDate: sameDay,
                            showButtonPanel: false,
                            dateFormat: deliveryDateFormat,
                            showOn: 'both',
                            buttonText: '',
                            beforeShowDay: function (date) {

                                var ksCurrentDay  = date.getDay();
                                var ksDateFormate = jQuery.datepicker.formatDate('yy/mm/dd', date);
                                var isDisableDate = isAvailableDate.indexOf(ksDateFormate) === -1
                                var isDisableDay  = deliveryDaysOff.indexOf(ksCurrentDay) === -1;

                                return [isDisableDay && isDisableDate];
                            }
                        };
                        $(element).datepicker(ks_options);
                    }
                };

                ksPrepareSubValue(this.ksDeliveryDate, cacheKeyDeliveryDate);
                ksPrepareSubValue(this.ksDeliverydateTimeSlot, cacheKeyDeliveryTime);
                ksPrepareSubValue(this.ksDeliverydateComment, cacheKeyDeliveryComment);

                this.isVisible = ko.computed(function () {
                    return !!self.ksDeliveryDate();
                });
                return this;
            },
        });
    }
);
