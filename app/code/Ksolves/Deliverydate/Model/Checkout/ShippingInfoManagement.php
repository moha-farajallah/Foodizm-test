<?php
/**
 * Ksolves
 *
 * @category    Ksolves
 * @package     Ksolves_Deliverydate
 * @copyright   Copyright (c) Ksolves (https://www.ksolves.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php
 */

namespace Ksolves\Deliverydate\Model\Checkout;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Ksolves\Deliverydate\Helper\Data;

/**
 * Class ShippingInfoManagement
 */
class ShippingInfoManagement
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @param Data $checkoutSession
     * @param Data $dataHelper
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        Data $dataHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->_dataHelper = $dataHelper;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param int $cartId
     * @param ShippingInformationInterface $addressInformation
     *
     * @return array
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
        
    ) {
        if ($this->_dataHelper->isEnabled() && $extensionAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes()) {

            $extensionAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();

            $deliveryInformation = [
                'ksDeliveryDate'        => $extensionAttributes->getKsDeliveryDate(),
                'ksDeliverydateTimeSlot'=> $extensionAttributes->getKsDeliveryTimeslot(),
                'ksDeliverydateComment' => $extensionAttributes->getKsDeliveryComment()
            ];
            $this->checkoutSession->setKsddData($deliveryInformation);
        }
        return [$cartId, $addressInformation];
    }
}
