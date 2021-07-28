define(
    [
    "uiComponent",
    "Magento_Checkout/js/model/payment/renderer-list"
    ],
    function (
        Component,
        rendererList
    ) {
    "use strict";
    rendererList.push(
        {
            type: "gmo_monthlycredit",
            component: "Gmo_Payments/js/view/payment/method-renderer/monthlycredit"
        }
    );

    return Component.extend({});
});
