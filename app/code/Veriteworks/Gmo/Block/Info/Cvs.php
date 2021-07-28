<?php
namespace Veriteworks\Gmo\Block\Info;

use \Magento\Framework\View\Element\Template\Context;
use \Veriteworks\Gmo\Model\Source\Cvstypes;

/**
 * Cvs info block
 */
class Cvs extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'Veriteworks_Gmo::info/cvs.phtml';

    /**
     * @var \Veriteworks\Gmo\Model\Source\Cvstypes
     */
    protected $_cvsTypes;

    /**
     * Cvs constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Veriteworks\Gmo\Model\Source\Cvstypes $cvstypes
     * @param array $data
     */
    public function __construct(
        Context $context,
        Cvstypes $cvstypes,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_cvsTypes = $cvstypes;
    }

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
            if (array_key_exists('Convenience', $additional)) {
                $source = $this->_cvsTypes;
                $data[__('CVS name')->__toString()] =
                    $source->getCvsType($additional['Convenience']);
            }

            if (array_key_exists('ConfNo', $additional)) {
                switch ($additional['Convenience']) {
                    case '10001':
                    case '10005':
                        $data[__('Conf Number')->__toString()] = $additional['ConfNo'];
                        break;
                    case '10002':
                        $data[__('Company Code')->__toString()] = $additional['ConfNo'];
                        break;
                    case '00006':
                        $data[__('Conf Number')->__toString()] = $additional['ConfNo'];
                        break;
                    case '10008':
                        $data[__('Accept Number')->__toString()] = $additional['ConfNo'];
                        break;
                }
            }

            if (array_key_exists('ReceiptNo', $additional)) {
                switch ($additional['Convenience']) {
                    case '10001':
                        $data[__('Customer Transaction Number')->__toString()] = $additional['ReceiptNo'];
                        break;
                    case '10002':
                        $data[__('Order Number')->__toString()] = $additional['ReceiptNo'];
                        break;
                    case '10005':
                        $label = __('Customer Transaction Number/Online Transaction Number')->__toString();
                        $data[$label] = $additional['ReceiptNo'];
                        break;
                    case '00006':
                        $data[__('Online Transaction Number')->__toString()] = $additional['ReceiptNo'];
                        break;
                    case '10008':
                        $data[__('Submit Number')->__toString()] = $additional['ReceiptNo'];
                        break;
                }
            }

            if (array_key_exists('ReceiptUrl', $additional)) {
                $data[__('Receipt URL')->__toString()] = $additional['ReceiptUrl'];
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
        }

        return $transport->setData(array_merge($transport->getData(), $data));
    }
}
