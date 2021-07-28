/**
 * Ksolves
 *
 * @category   Ksolves
 * @package    Ksolves_Deliverydate
 * @author     Ksolves Team
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/set-shipping-information': {
                'Ksolves_Deliverydate/js/action/set-ks-shipping-info-mixin': true
            }
        }
    }
};