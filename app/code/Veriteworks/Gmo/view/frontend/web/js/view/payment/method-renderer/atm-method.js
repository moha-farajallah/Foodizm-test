/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function ($, Component, placeOrderAction, fullScreenLoader, additionalValidators) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Veriteworks_Gmo/payment/atm'
            },

            /**
             * Init component
             */
            initialize: function () {
                var self = this;

                this._super();

            },

            getCode: function() {
                return 'veritegmo_atm';
            },

            /**
             * Get payment method data
             */
            getData: function () {
                return {
                    'method': this.item.method
                };
            },


            isActive: function() {
                return true;
            },

            validate: function() {
                var $form = $('#' + this.getCode() + '-form');
                return $form.validation() && $form.validation('isValid');
            },
            /** Returns payment method instructions */
            getInstructions: function() {
                return window.checkoutConfig.payment.instructions['veritegmo_atm'];
            }

        });
    }
);
