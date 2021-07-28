<?php

namespace Scrumwheel\PaymentCost\Model\Order\Total\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;

class Fee extends AbstractTotal
{
    protected $logger;

    public function __construct(
        \Psr\Log\LoggerInterface $loggerInterface
    )
    {
        $this->logger = $loggerInterface;
    }

    public function collect(\Magento\Sales\Model\Order\Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $feeAmountInvoiced = $order->getFeeAmountInvoiced();
        $baseFeeAmountInvoiced = $order->getBaseFeeAmountInvoiced();


        if((int)$feeAmountInvoiced === 0){
            return $this;
        }


        $feeAmountRefunded = $order->getFeeAmountRefunded();
        if((int)$feeAmountRefunded === 0){
            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmountInvoiced);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $baseFeeAmountInvoiced);
            $creditmemo->setFeeAmount($feeAmountInvoiced);
            $creditmemo->setBaseFeeAmount($baseFeeAmountInvoiced);


            $order->setFeeAmountRefunded($feeAmountInvoiced);
            $order->setBaseFeeAmountRefunded($baseFeeAmountInvoiced);
        }

        return $this;
    }
}
