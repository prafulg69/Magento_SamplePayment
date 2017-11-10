/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
     [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Paypal/js/action/set-payment-method',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/customer-data'
    ],
    function (
        $,
        Component,
        setPaymentMethodAction,
        additionalValidators,
        quote,
        customerData
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Ccmt_PaymentGateway/payment/form',
                transactionResult: ''
            },

            initObservable: function () {

                this._super()
                    .observe([
                        'transactionResult'
                    ]);
                return this;
            },

            getCode: function() {
                return 'sample_gateway';
            },

            getData: function() {
                return {
                    'method': this.item.method,
                    'additional_data': {
                        'transaction_result': this.transactionResult()
                    }
                };
            },

             /** Returns payment acceptance mark link path */
            getPaymentAcceptanceMarkHref: function () {
                return 'AxisBank Gateway';
            },

            /** Returns payment acceptance mark image path */
            getPaymentAcceptanceMarkSrc: function () {
                return "Magento_SamplePaymentGateway/images/axis-bank.png" ;
            },


            /** Redirect to paypal */
            continueToAxisBank: function () {
                if (additionalValidators.validate()) {
                    //update payment method information if additional data was changed
                    this.selectPaymentMethod();
                    setPaymentMethodAction(this.messageContainer).done(
                        function () {
                            customerData.invalidate(['cart']);
                            /*$.mage.redirect(
                                window.checkoutConfig.payment.paypalExpress.redirectUrl[quote.paymentMethod().method]
                            );*/
                    $.mage.redirect('https://migs.mastercard.com.au/vpcpay');
                        }
                    );

                    return false;
                }
            },

            getTransactionResults: function() {
                return _.map(window.checkoutConfig.payment.sample_gateway.transactionResults, function(value, key) {
                    return {
                        'value': key,
                        'transaction_result': value
                    }
                });
            }
        });
    }
);