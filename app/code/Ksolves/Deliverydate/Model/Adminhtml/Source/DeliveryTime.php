<?php
/**
 * Ksolves
 *
 * @category    Ksolves
 * @package     Ksolves_Deliverydate
 * @copyright   Copyright (c) Ksolves (https://www.ksolves.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php
 */

namespace Ksolves\Deliverydate\Model\Adminhtml\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class DeliveryTime
 */
class DeliveryTime implements ArrayInterface
{
    const KSOLVES_D_M_Y_DASH  = 'dd-mm-yy';
    const KSOLVES_FULL_FORM   = 'DD, d MM, yy';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __('Day-Month-Year (%1)', date('d-m-Y')),
                'value' => self::KSOLVES_D_M_Y_DASH
            ],
            [
                'label' => __('DD, d MM, yy (%1)', date('l, d F, Y')),
                'value' => self::KSOLVES_FULL_FORM
            ]
        ];
        return $options;
    }
}
