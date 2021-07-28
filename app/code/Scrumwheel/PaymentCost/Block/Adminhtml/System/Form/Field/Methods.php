<?php

namespace Scrumwheel\PaymentCost\Block\Adminhtml\System\Form\Field;

class Methods extends \Magento\Framework\View\Element\Html\Select
{

    private $methods;

    protected $paymentConfig;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Payment\Model\Config $config,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->paymentConfig = $config;

    }

    protected function _getPaymentMethods()
    {
        if($this->methods === null) {
            $this->methods = $this->paymentConfig->getActiveMethods();
        }
        return $this->methods;
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->_getPaymentMethods() as $paymentCode => $paymentModel) {
                $paymentTitle = $this->_scopeConfig->getValue('payment/'.$paymentCode.'/title');
                $this->addOption($paymentCode, addslashes($paymentTitle));
                $this->setExtraParams('style="width:180px;"');
            }
        }
        return parent::_toHtml();
    }
}
