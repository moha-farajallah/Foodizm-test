<?php
/**
 * Ksolves
 *
 * @category    Ksolves
 * @package     Ksolves_Deliverydate
 * @copyright   Copyright (c) Ksolves (https://www.ksolves.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php
 */


namespace Ksolves\Deliverydate\Block\Sales\Order\View;

use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Ksolves\Deliverydate\Helper\Data;

/**
 * Class DeliveryDateInfo
 */
class DeliveryDateInfo extends Template
{
    /**
     * @type Registry|null
     */
    protected $registry = null;

    /**
     * @var Data
     */
    protected $_dataHelper;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Data $dataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Data $dataHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->_dataHelper = $dataHelper;

        parent::__construct($context, $data);
    }

    /**
     * Get deliverydate information 
     *
     * @return DataObject
     */
    public function getDeliverydateInformation()
    {
        $result = [];

        if ($order = $this->getOrder()) {
            $ksDeliverydateInfo = $order->getKsDeliveryInfo();
            
            if (is_array(json_decode($ksDeliverydateInfo, true))) {
                $result = json_decode($ksDeliverydateInfo, true);
            } else {
                $values = explode(' ', $ksDeliverydateInfo);
                if (sizeof($values) > 1) {
                    $result['ksDeliveryDate'] = $values[0];
                    $result['ksDeliverydateTimeSlot'] = $values[1];
                }
            }
        }
        return new DataObject($result);
    }

    /**
     * Get current order
     *
     * @return mixed
     */
    public function getOrder()
    {
        return $this->registry->registry('current_order');
    }


    /**
     * Get Deliverydate Label
     *
     * @return string
     */
    public function getDeliveryLabel()
    {
        return $this->_dataHelper->getDeliveryLabel();
    }

    /**
     * Get Deliverydate TimeSlot Label
     *
     * @return string
     */
    public function getDeliveryTimeSlotLabel()
    {
        return $this->_dataHelper->getDeliveryTimeSlotLabel();
    }

    /**
     * Get Deliverydate Comment Label
     *
     * @return string
     */
    public function getDeliveryCommentLabel()
    {
        return $this->_dataHelper->getDeliveryCommentLabel();
    }
}
