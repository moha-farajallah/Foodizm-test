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
                template: 'Veriteworks_Gmo/payment/cvs'
            },

            initObservable: function () {
                this._super()
                    .observe([
                        'cvsType'
                    ]);

                return this;
            },

            /**
             * Init component
             */
            initialize: function () {
                var self = this;

                this._super();

            },

            getCode: function() {
                return 'veritegmo_cvs';
            },

            /**
             * Get payment method data
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'cvs_type': this.cvsType()
                    }
                };
            },

            getCvsTypes: function() {
                return window.checkoutConfig.payment.veritegmo_cvs.availableTypes;
            },

            /**
             * Get list of available month values
             * @returns {Object}
             */
            getCvsValues: function () {
                return _.map(this.getCvsTypes(), function (value, key) {
                    return {
                        'value': key,
                        'cvs': value
                    };
                });
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
                return window.checkoutConfig.payment.instructions['veritegmo_cvs'];
            }

        });
    }
);
