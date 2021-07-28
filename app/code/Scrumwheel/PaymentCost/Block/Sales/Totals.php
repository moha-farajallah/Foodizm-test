<?php

namespace Scrumwheel\PaymentCost\Block\Sales;

class Totals extends \Magento\Framework\View\Element\Template
{
    protected $_order;

    protected $_source;

    public function displayFullSummary()
    {
        return true;
    }

    public function getSource()
    {
        return $this->_source;
    }
    public function getStore()
    {
        return $this->_order->getStore();
    }

    public function getOrder()
    {
        return $this->_order;
    }

    public function initTotals()
    {

        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        if(!$this->_source->getFeeAmount()) {
            return $this;
        }

        $fee = new \Magento\Framework\DataObject(
            [
                'code' => 'fee',
                'strong' => false,
                'value' => $this->_source->getFeeAmount(),
                'label' => __('手数料 [Settlement Fee]'),
            ]
        );

        $parent->addTotal($fee, 'fee');

        return $this;
    }
}
