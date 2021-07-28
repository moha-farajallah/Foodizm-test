/**
 * Ksolves
 *
 * @category   Ksolves
 * @package    Ksolves_Deliverydate
 * @author     Ksolves Team
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
    'Ksolves_Deliverydate/js/model/ks-info'
], function ($, wrapper, quote, ksdeliveryDateInformation) {
    'use strict';

    return function (setShippingInformationAction) {
        if (!window.checkoutConfig || !window.checkoutConfig.ksDeliverydateConfig) {
            return setShippingInformationAction;
        }

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();

            if (!shippingAddress.hasOwnProperty('extension_attributes')) {
                shippingAddress.extension_attributes = {};
            }

            var deliveryData = {
                ks_delivery_date: ksdeliveryDateInformation().ksDeliveryDate(),
                ks_delivery_timeslot: ksdeliveryDateInformation().ksDeliverydateTimeSlot(),
                ks_delivery_comment: ksdeliveryDateInformation().ksDeliverydateComment()
            };

            shippingAddress.extension_attributes = $.extend(
                shippingAddress.extension_attributes,
                deliveryData
            );

            return originalAction();
        });
    };
});