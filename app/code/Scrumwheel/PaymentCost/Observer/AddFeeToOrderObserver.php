<?php

namespace Scrumwheel\PaymentCost\Observer;

use Scrumwheel\PaymentCost\Helper\Data;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddFeeToOrderObserver implements ObserverInterface
{
    protected $_checkoutSession;
    protected $_helper;
    protected $logger;
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        Data $helper,
        \Psr\Log\LoggerInterface $loggerInterface
    )
    {
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->logger = $loggerInterface;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getQuote();
        if ($this->_helper->canApply($quote)) {
            $feeAmount = $this->_helper->getFee($quote);


            $order = $observer->getOrder();
            $order->setData('fee_amount', $feeAmount);
            $order->setData('base_fee_amount', $feeAmount);
        }

        return $this;
    }
}
