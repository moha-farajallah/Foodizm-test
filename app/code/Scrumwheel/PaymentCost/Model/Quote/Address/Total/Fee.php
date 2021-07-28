<?php

namespace Scrumwheel\PaymentCost\Model\Quote\Address\Total;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    protected $_code = 'fee';
    protected $_helperData;
    protected $_checkoutSession;
    protected $logger;
    protected $_quoteValidator = null;
    public function __construct(
        \Magento\Quote\Model\QuoteValidator $quoteValidator,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\Data\PaymentInterface $payment,
        \Scrumwheel\PaymentCost\Helper\Data $helperData,
        \Psr\Log\LoggerInterface $loggerInterface
    )
    {
        $this->_quoteValidator = $quoteValidator;
        $this->_helperData = $helperData;
        $this->_checkoutSession = $checkoutSession;
        $this->logger = $loggerInterface;
    }
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        if (!count($shippingAssignment->getItems())) {
            return $this;
        }

        $fee = 0;
        if($this->_helperData->canApply($quote)) {
            $fee = $this->_helperData->getFee($quote);
        }

        $total->setFeeAmount($fee);
        $total->setBaseFeeAmount($fee);

        $total->setTotalAmount('fee_amount', $fee);
        $total->setBaseTotalAmount('base_fee_amount', $fee);
        $total->setGrandTotal($total->getGrandTotal());
        $total->setBaseGrandTotal($total->getBaseGrandTotal());
        $quote->setFeeAmount($fee);
        $quote->setBaseFeeAmount($fee);

        return $this;
    }
    public function fetch(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        $result = [
            'code' => $this->getCode(),
            'title' => __('手数料 [Settlement Fee]'),
            'value' => $total->getFeeAmount()
        ];
        return $result;
    }
    public function getLabel()
    {
        return __('手数料 [Settlement Fee]');
    }
}
