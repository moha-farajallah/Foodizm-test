
define(
    [
        'Scrumwheel_PaymentCost/js/view/cart/summary/fee'
    ],
    function (Component) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Scrumwheel_PaymentCost/cart/totals/fee'
            },
            isDisplayed: function () {
                return this.getPureValue() != 0;
            }
        });
    }
);
