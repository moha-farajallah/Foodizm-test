/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/action/place-order',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Payment/js/model/credit-card-validation/validator',
        'mage/url',
        'mage/translate',
        'Magento_Ui/js/modal/alert'
    ],
    function ($, Component, placeOrderAction, fullScreenLoader, additionalValidators, ccValidator, url, $t, alert) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Veriteworks_Gmo/payment/cc',
                cardOwner: ''
            },

            /**
             * Init component
             */
            initialize: function () {
                var self = this;

                this._super();

                var gm = document.createElement( 'script' );
                gm.type = 'text/javascript';
                gm.src = this.getGatewayUrl();
                var s = document.head;
                s.appendChild( gm, s );

            },

            initObservable: function () {
                this._super().observe([
                        'creditCardType',
                        'creditCardExpYear',
                        'creditCardExpMonth',
                        'creditCardNumber',
                        'creditCardVerificationNumber',
                        'paymentType',
                        'splitCount',
                        'registerCard',
                        'useCard',
                        'creditCardToken',
                        'selectedCardType',
                        'holderName',
                        'tokenError'
                    ]);

                return this;
            },

            getCode: function() {
                return 'veritegmo_cc';
            },

            /**
             * Get data
             * @returns {Object}
             */
            getData: function () {
                var additional;

                if(this.getUseToken() == '1'){
                    additional = {
                        'payment_type': this.paymentType(),
                        'split_count': this.splitCount(),
                        'use_card' : this.useCard(),
                        'is_use_card': this.isUseCard(),
                        'cc_token': jQuery('#veritegmo_cc_cc_token').val()
                    };
                } else {
                    additional = {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber(),
                        'payment_type': this.paymentType(),
                        'use_card' : this.useCard(),
                        'is_use_card': this.isUseCard(),
                        'split_count': this.splitCount()
                    };
                }

                return {
                    'method': this.item.method,
                    'additional_data': additional
                };
            },

            canUseSplit: function() {
                return window.checkoutConfig.payment.veritegmo_cc.can_use_split;
            },

            getPaymentTypes: function() {
                return window.checkoutConfig.payment.veritegmo_cc.payment_type;
            },

            getShopId: function () {
                return window.checkoutConfig.payment.veritegmo_cc.shop_id;
            },

            getPaymentTypeValues: function () {
                return _.map(this.getPaymentTypes(), function (value, key) {
                    return {
                        'value': key,
                        'type': value
                    };
                });
            },

            getUseToken: function() {
                return window.checkoutConfig.payment.veritegmo_cc.use_token;
            },

            getUseHolderName: function() {
                return window.checkoutConfig.payment.veritegmo_cc.use_holder_name;
            },

            canRegisterCard: function() {
                return window.checkoutConfig.payment.veritegmo_cc.can_register_card;
            },

            getSplitCount: function() {
                return window.checkoutConfig.payment.veritegmo_cc.split_count;
            },

            isSplit: function() {
                if(this.paymentType() == '2'){
                    return true;
                }

                return false;
            },

            isUseCard: function() {
                if(this.useCard() != undefined && this.useCard() != '99'){
                    return true;
                }

                return false;
            },

            canUseRegisteredCard: function() {
                return window.checkoutConfig.payment.veritegmo_cc.can_use_registerd_card;
            },

            getRegisteredCards: function() {
                return window.checkoutConfig.payment.veritegmo_cc.registered_cards;
            },

            getRegisteredCardValues: function() {
                return _.map(this.getRegisteredCards(), function (value, key) {
                    return {
                        'value': value,
                        'text': key
                    };
                });
            },

            getGatewayUrl: function() {
                return window.checkoutConfig.payment.veritegmo_cc.gateway_url;
            },

            getSplitCountValues: function () {
                return _.map(this.getSplitCount(), function (value, key) {
                    return {
                        'value': key,
                        'count': value
                    };
                });
            },

            isActive: function() {
                return true;
            },

            placeOrder: function (data, event) {
                var self = this;

                if (event) {
                    event.preventDefault();
                }

                if (this.validate() && additionalValidators.validate()) {
                    this.isPlaceOrderActionAllowed(false);

                    if(!this.isUseCard() && this.getUseToken() == '1') {
                        Multipayment.init(this.getShopId());
                        Multipayment.getToken(
                            {
                                cardno: this.creditCardNumber(),
                                holdername: this.holderName(),
                                expire: (this.creditCardExpYear() + ("00"+this.creditCardExpMonth()).slice(-2)).replace(/[^\d]/g, ""),
                                securitycode: this.creditCardVerificationNumber().replace(/[^\d]/g, "")
                            }, submitToGmo);

                        var timer = setInterval(function(obj) {
                            var token, error;
                            token = jQuery('#veritegmo_cc_cc_token').val();
                            error = jQuery('#veritegmo_cc_cc_error').val();

                            if(token) {
                                clearInterval(timer);
                                obj.getPlaceOrderDeferredObject().fail(
                                    function () {
                                        self.isPlaceOrderActionAllowed(true);
                                    }
                                ).done(
                                    function () {
                                        if (self.redirectAfterPlaceOrder) {
                                            window.location.replace(url.build('gmo/acs/send/'));
                                        }
                                    });
                            } else if(error) {
                                clearInterval(timer);
                                //alert({content: $t("Please confirm your credit card information.")});
								alert({content: $t(error)});
                                self.isPlaceOrderActionAllowed(true);
                            }
                        }, 1000, self);

                    } else {
                        this.getPlaceOrderDeferredObject()
                            .fail(
                                function () {
                                    self.isPlaceOrderActionAllowed(true);
                                }
                            ).done(
                            function () {
                                if (self.redirectAfterPlaceOrder) {
                                    window.location.replace(url.build('gmo/acs/send/'));
                                }
                            }
                        );
                    }
                    return true;
                }
                return false;
            },

            validate: function() {
                var $form = $('#veritegmo-cc-form');
                return $form.validation() && $form.validation('isValid');
            }


        });
    }
);
