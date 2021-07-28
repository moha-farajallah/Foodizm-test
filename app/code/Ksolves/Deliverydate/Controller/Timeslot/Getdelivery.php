<?php
/**
 * Ksolves
 *
 * @category    Ksolves
 * @package     Ksolves_Deliverydate
 * @copyright   Copyright (c) Ksolves (https://www.ksolves.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
namespace Ksolves\Deliverydate\Controller\Timeslot;
use Ksolves\Deliverydate\Helper\Data;
/**
 * class Getdelivery
 */
class Getdelivery extends \Magento\Framework\App\Action\Action
{
    
    /**
     * @var Data
     */
    protected $_dataHelper;
    
    /**
     * CheckoutConfigProvider constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param Data $dataHelper
     */
    public function __construct(
       \Magento\Framework\App\Action\Context $context,
       Data $dataHelper
    ) {
        parent::__construct($context);
        $this->_dataHelper = $dataHelper;
    }

    public function execute()
    {
      $current_date  = $this->getRequest()->getParam('today_date');
      $newDate = date("d-m-Y", strtotime($current_date));
      $finalDisableTimeSlot  = $this->_dataHelper->getFinalAddTimeSlotOption($newDate);
      $response = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
      $response->setContents(json_encode($finalDisableTimeSlot));
      return $response;
    }
}
