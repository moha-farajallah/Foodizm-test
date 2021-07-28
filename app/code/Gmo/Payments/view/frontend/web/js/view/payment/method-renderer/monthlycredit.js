/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* @api */
define([
    'ko',
    'Magento_Checkout/js/view/payment/default',
], function (ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Gmo_Payments/payment/monthlycredit',
            code: 'gmo_monthlycredit'
        },

        /**
         * Get value of instruction field.
         * @returns {String}
         */
        getInstructions: function () {
            return window.checkoutConfig.payment.instructions[this.item.method];
        },

        /**
         * Get payment name
         *
         * @returns {String}
         */
        getCode: function () {
            return this.code;
        }
    });
});
