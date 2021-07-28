<?php
/**
 * Ksolves
 *
 * @category    Ksolves
 * @package     Ksolves_Deliverydate
 * @copyright   Copyright (c) Ksolves (https://www.ksolves.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php
 */

namespace Ksolves\Deliverydate\Block\Adminhtml\Plugin\Order;

/**
 * Class OrderViewTabInfo
 */
class OrderViewTabInfo
{
    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View\Tab\Info $subject
     * @param $result
     *
     * @return string
     */
    public function afterGetGiftOptionsHtml(\Magento\Sales\Block\Adminhtml\Order\View\Tab\Info $subject, $result)
    {
        $result .= $subject->getChildHtml('ks_delivery_information');

        return $result;
    }
}
