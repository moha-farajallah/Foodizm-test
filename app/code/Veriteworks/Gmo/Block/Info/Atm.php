<?php
namespace Veriteworks\Gmo\Block\Info;

use \Magento\Framework\View\Element\Template\Context;

/**
 * Atm info block
 */
class Atm extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Veriteworks_Gmo::info/atm.phtml';

    /**
     * @param null $transport
     * @return \Magento\Framework\DataObject
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);
        $additional = $this->getInfo()->getAdditionalInformation();
        $data = [];

        if (is_array($additional)) {
            if (array_key_exists('BkCode', $additional)) {
                $data[__('Bank Code')->__toString()] = $additional['BkCode'];
            }

            if (array_key_exists('CustID', $additional)) {
                $data[__('Customer ID')->__toString()] = $additional['CustID'];
            }

            if (array_key_exists('ConfNo', $additional)) {
                $data[__('Confirmation Number')->__toString()] =
                    $additional['ConfNo'];
            }

            if (array_key_exists('PaymentTerm', $additional)) {
                $paydate = $additional['PaymentTerm'];
                $data[__('Payment limit date')->__toString()] =
                    preg_replace(
                        '/^(\d\d\d\d)(\d\d)(\d\d)(\d\d)(\d\d)(\d\d)$/',
                        '$1/$2/$3 $4:$5:$6',
                        $paydate
                    );
            }
            if (array_key_exists('PaymentURL', $additional)) {
                $payurl = $additional['PaymentURL'];
                $text = '<a href="'. $payurl . '" target="_blank">' . __('Go Payment Page') . '</a>';
                $data[__('Payment Page URL')->__toString()] = $text;
            }
        }

        return $transport->setData(array_merge($transport->getData(), $data));
    }
}
