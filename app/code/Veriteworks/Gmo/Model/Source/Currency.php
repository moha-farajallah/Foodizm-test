<?php
namespace Veriteworks\Gmo\Model\Source;

/**
 * Class Currency
 * @package Veriteworks\Gmo\Model\Source
 */
class Currency
{

    /**
     * @var array
     */
    protected $_options;

    /**
     * @var \Magento\Framework\Locale\ListsInterface
     */
    protected $_localeLists;

    /**
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     */
    public function __construct(\Magento\Framework\Locale\ListsInterface $localeLists)
    {
        $this->_localeLists = $localeLists;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $services = $this->_localeLists->getOptionCurrencies();
        $availableCurrency = [
            'USD',
            'CAD',
            'HKD',
            'SGD',
            'KRW',
            'TWD',
            'CNY',
            'AUD',
            'MYR',
            'THB',
            'INR',
            'PHP',
            'VND',
            'EUR',
            'GBP',
            'RUB',
            'CHF',
            'NOK',
            'SEK',
            'DKK',
            'BRL',
            ];
        $this->_options = [];
        foreach ($services as $_code => $_options) {
            if (in_array($_options['value'], $availableCurrency)) {
                $this->_options[] = $_options;
            }
        }

        return $this->_options;
    }
}
