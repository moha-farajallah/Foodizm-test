<?php

namespace Scrumwheel\PaymentCost\Helper;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    const CONFIG_PAYMENT_FEE = 'paymentfee/config/';
    const TOTAL_CODE = 'fee_amount';
    public $methodFee = NULL;
    protected $serializer;
    protected $pricingHelper;
    protected $priceCurrency;
    protected $logger;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        SerializerInterface $serializer,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Directory\Model\PriceCurrency $priceCurrency,
        \Psr\Log\LoggerInterface $loggerInterface
    )
    {
        parent::__construct($context);
        $this->serializer = $serializer;
        $this->_getMethodFee();
        $this->pricingHelper = $pricingHelper;
        $this->priceCurrency = $priceCurrency;
        $this->logger = $loggerInterface;
    }

    public function _getMethodFee()
    {

        if (is_null($this->methodFee)) {
            $initialFees = $this->getConfig('fee');
            $fees = is_array($initialFees) ? $initialFees : $this->serializer->unserialize($initialFees);

            if (is_array($fees)) {
                foreach ($fees as $fee) {
                    $this->methodFee[$fee['payment_method']] = array(
                        'fee' => $fee['fee'],
                        'is_active' => $fee['is_active'],
                        'calculate_fee' => $fee['calculate_fee'],
                    );
                }
            }

        }
        return $this->methodFee;
    }

    public function getConfig($field = '')
    {
        if ($field) {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            return $this->scopeConfig->getValue(self::CONFIG_PAYMENT_FEE . $field, $storeScope);
        }
        return NULL;
    }

    public function isEnabled()
    {
        return $this->getConfig('enabled');
    }

    public function canApply(\Magento\Quote\Model\Quote $quote)
    {

        if ($this->isEnabled()) {
            if ($method = $quote->getPayment()->getMethod()) {
                if (isset($this->methodFee[$method])) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getFee(\Magento\Quote\Model\Quote $quote)
    {

        $method = $quote->getPayment()->getMethod();
        $fee = 0;
		$subTotalNoTax = $quote->getSubtotal();
		$subTotal = $subTotalNoTax + ($subTotalNoTax * 0.10);
        if($this->methodFee[$method]['is_active'] == 1) {
            $fee = $this->methodFee[$method]['fee'];
			switch (true) {
				case $subTotal <= 11000:
					$fee = 330;
					break;
				case $subTotal <= 33000:
					$fee = 440;
					break;
				case $subTotal <= 110000:
					$fee = 660;
					break;
				case $subTotal > 110000:
					$fee = 1100;
					break;
				default:
					$fee = 330;
					break;
			}		
			
            $feeType = $this->methodFee[$method]['calculate_fee'];

            if ($feeType != \Magento\Shipping\Model\Carrier\AbstractCarrier::HANDLING_TYPE_FIXED) {			
                $fee = $subTotal * ($fee / 100);
            }
            $fee = $this->priceCurrency->round($fee);
        }
        return $fee;
    }
}
