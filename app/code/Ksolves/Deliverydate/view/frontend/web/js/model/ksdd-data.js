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
        'jquery',
        'Magento_Customer/js/customer-data'
    ],
    function ($, storage) {
        'use strict';

        var cacheKey = 'ksdd-data';

        var getData = function () {
            return storage.get(cacheKey)();
        };

        var saveData = function (checkoutData) {
            storage.set(cacheKey, checkoutData);
        };
        return {
            setData: function (key, data) {
                var obj = getData();
                obj[key] = data;
                saveData(obj);
            },

            getData: function (key) {
                if (typeof key === 'undefined') {
                    return getData();
                }

                return getData()[key];
            }
        }
    }
);
