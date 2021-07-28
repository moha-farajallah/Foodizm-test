/**
 * Ksolves
 *
 * @category   Ksolves
 * @package    Ksolves_Deliverydate
 * @author     Ksolves Team
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

define(
    [
        'ko',
        'uiComponent'
    ],
    function (ko, Component) {
        'use strict';

        return Component.extend({
            ksDeliveryDate: ko.observable(),
            ksDeliverydateTimeSlot: ko.observable(),
            ksDeliverydateComment: ko.observable()
        });
    }
);
