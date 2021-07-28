<?php
namespace Veriteworks\Gmo\Block\Info;

/**
 * Cc info block
 */
class Cc extends \Magento\Payment\Block\Info\Cc
{
    /**
     * @var string
     */
    protected $_template = 'Veriteworks_Gmo::info/cc.phtml';

    /**
     * Prepare credit card related payment info
     *
     * @param \Magento\Framework\DataObject|array $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = new \Magento\Framework\DataObject();
        $data = [];

        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
