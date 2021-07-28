<?php

namespace Scrumwheel\PaymentCost\Model\Order\Total\Invoice;

use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

class Fee extends AbstractTotal
{

    protected $logger;

    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface
    )
    {
        $this->logger = $loggerInterface;
    }

    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $order = $invoice->getOrder();
        $feeAmount = $order->getFeeAmount();
        $baseFeeAmount = $order->getBaseFeeAmount();

        $invoice->setFeeAmount($feeAmount);
        $invoice->setBaseFeeAmount($baseFeeAmount);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $feeAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseFeeAmount);

        $order->setFeeAmountInvoiced($feeAmount);
        $order->setBaseFeeAmountInvoiced($baseFeeAmount);

        return $this;
    }
}
