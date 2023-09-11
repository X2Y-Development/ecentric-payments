define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
    ],
    function (Component, urlBuilder) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Ecentric_Payment/payment/ecentric'
            },

            redirectAfterPlaceOrder: false,

            /**
             * After place order callback
             */
            afterPlaceOrder: function () {
                window.location.replace(urlBuilder.build('ecentric/checkout/redirect'));
            },

            getPaymentLogoSrc: function () {
                return require.toUrl('Ecentric_Payment/images/logo.jpg');
            },
        });
    }
);
