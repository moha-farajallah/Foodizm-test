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

use Magento\Store\Model\StoreManagerInterface;
use Ksolves\Deliverydate\Helper\Data;

/**
 * class CheckoutConfigProvider
 */
class CheckoutConfigProvider implements \Magento\Checkout\Model\ConfigProviderInterface
{
    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CheckoutConfigProvider constructor.
     *
     * @param Data $dataHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $dataHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->_dataHelper = $dataHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        if (!$this->_dataHelper->isEnabled()) {
            return [];
        }

        $output = [
            'ksDeliverydateConfig' => $this->getKsCheckoutConfig()
        ];
        return $output;
    }

     /**
     * @return array
     * @throws \Zend_Serializer_Exception
     */
    public function getDisableDate()
    {
        $_isDisableDate  = $this->_dataHelper->getExcludeDates();
  
        if (!empty($_isDisableDate) && count($_isDisableDate) > 0) {
            return $_isDisableDate;
        }else{
            return [];
        }
    }

    /**
     * @return array
     * @throws \Zend_Serializer_Exception
     */
    private function getKsCheckoutConfig()
    {
        return [
            'isEnabledDeliveryTime'      => $this->_dataHelper->isEnabled(),
            'getDeliveryLabel'           => $this->_dataHelper->getDeliveryLabel(),
            'getToadyDate'               => $this->_dataHelper->getToadyDate(),
            'isEnabledDeliveryTimeSlot'  => $this->_dataHelper->isEnabledDeliveryTimeSlot(),
            'getDeliveryTimeSlotLabel'   => $this->_dataHelper->getDeliveryTimeSlotLabel(),
            'isEnabledDeliveryComment'   => $this->_dataHelper->isEnabledDeliveryComment(),
            'getDeliveryCommentLabel'    => $this->_dataHelper->getDeliveryCommentLabel(),
            'deliveryDateFormat'         => $this->_dataHelper->getDateFormat(),
            'deliveryDaysOff'            => $this->_dataHelper->getDaysOff(),
            'deliveryDateOff'            => $this->getDisableDate()
            
        ];
    }
}
