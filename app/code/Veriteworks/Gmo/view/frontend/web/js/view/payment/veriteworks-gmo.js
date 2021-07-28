/*browser:true*/
/*global define*/
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
                type: 'veritegmo_cc',
                component: 'Veriteworks_Gmo/js/view/payment/method-renderer/cc-method'
            }

        );
        rendererList.push(
            {
                type: 'veritegmo_ccmulti',
                component: 'Veriteworks_Gmo/js/view/payment/method-renderer/ccmulti-method'
            }

        );
        rendererList.push(
            {
                type: 'veritegmo_cvs',
                component: 'Veriteworks_Gmo/js/view/payment/method-renderer/cvs-method'
            }

        );

        rendererList.push(
            {
                type: 'veritegmo_atm',
                component: 'Veriteworks_Gmo/js/view/payment/method-renderer/atm-method'
            }

        );

        /** Add view logic here if needed */
        return Component.extend({});
    }
);