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
                template: 'Veriteworks_Gmo/payment/ccmulti',
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
                        'creditCardToken',
                        'selectedCardType',
                        'holderName',
                        'tokenError'
                    ]);

                return this;
            },

            getCode: function() {
                return 'veritegmo_ccmulti';
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
                        'cc_token': jQuery('#veritegmo_ccmulti_cc_token').val(),
                    };
                } else {
                    additional = {
                        'cc_cid': this.creditCardVerificationNumber(),
                        'cc_type': this.creditCardType(),
                        'cc_exp_year': this.creditCardExpYear(),
                        'cc_exp_month': this.creditCardExpMonth(),
                        'cc_number': this.creditCardNumber()
                    };
                }

                return {
                    'method': this.item.method,
                    'additional_data': additional
                };
            },

            getShopId: function () {
                return window.checkoutConfig.payment.veritegmo_ccmulti.shop_id;
            },

            getUseToken: function() {
                return window.checkoutConfig.payment.veritegmo_ccmulti.use_token;
            },

            getUseHolderName: function() {
                return window.checkoutConfig.payment.veritegmo_cc.use_holder_name;
            },

            getGatewayUrl: function() {
                return window.checkoutConfig.payment.veritegmo_ccmulti.gateway_url;
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

                    if(this.getUseToken() == '1') {
                        Multipayment.init(this.getShopId());
                        Multipayment.getToken(
                            {
                                cardno: this.creditCardNumber(),
                                expire: (this.creditCardExpYear() + ("00"+this.creditCardExpMonth()).slice(-2)).replace(/[^\d]/g, ""),
                                holdername: this.holderName(),
                                securitycode: this.creditCardVerificationNumber().replace(/[^\d]/g, "")
                            }, submitToGmoMulti);

                        var timer = setInterval(function(obj) {
                            var token, error;
                            token = jQuery('#veritegmo_ccmulti_cc_token').val();
                            error = jQuery('#veritegmo_ccmulti_cc_error').val();

                            if(token) {
                                clearInterval(timer);
                                obj.getPlaceOrderDeferredObject().fail(
                                    function () {
                                        self.isPlaceOrderActionAllowed(true);
                                    }
                                ).done(
                                    function () {
                                        if (self.redirectAfterPlaceOrder) {
                                            window.location.replace(url.build('gmo/mcp/send/'));
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
                                    window.location.replace(url.build('gmo/mcp/send/'));
                                }
                            }
                        );
                    }
                    return true;
                }
                return false;
            },

            validate: function() {
                var $form = $('#veritegmo-ccmulti-form');
                return $form.validation() && $form.validation('isValid');
            }


        });
    }
);
