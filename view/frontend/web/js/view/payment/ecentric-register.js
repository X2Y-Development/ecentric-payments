define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';

        rendererList.push(
            {
                type: 'ecentric',
                component: 'Ecentric_Payment/js/view/payment/method-renderer/ecentric'
            },
        );

        return Component.extend({});
    }
);
