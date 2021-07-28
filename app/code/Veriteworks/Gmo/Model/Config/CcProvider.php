<?php
namespace Veriteworks\Gmo\Model\Config;

use Magento\Payment\Model\CcGenericConfigProvider;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;
use Veriteworks\Gmo\Model\Method\Cc;

/**
 * Class CcProvider
 * @package Veriteworks\Gmo\Model\Config
 */
class CcProvider extends CcGenericConfigProvider
{

    const CODE = Cc::CODE;

    /**
     * @var \Veriteworks\Gmo\Model\Source\Paymenttype
     */
    private $paymenttype;

    /**
     * @var \Veriteworks\Gmo\Model\Source\Splittype
     */
    private $splitCount;

    /**
     * @var \Veriteworks\Gmo\Model\Card\Lists
     */
    private $lists;

    /**
     * @var array
     */
    private $cards;

    /**
     * @var \Veriteworks\Gmo\Helper\Data
     */
    private $config;

    /**
     * ConfigProvider constructor.
     * @param \Magento\Payment\Model\CcConfig $ccConfig
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Veriteworks\Gmo\Helper\Data $helper
     * @param \Veriteworks\Gmo\Model\Card\Lists $lists
     * @param \Veriteworks\Gmo\Model\Source\Paymenttype $paymenttype
     * @param \Veriteworks\Gmo\Model\Source\Splittype $splitcount
     * @param array $methodCodes
     */
    public function __construct(
        CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        \Veriteworks\Gmo\Helper\Data $helper,
        \Veriteworks\Gmo\Model\Card\Lists $lists,
        \Veriteworks\Gmo\Model\Source\Paymenttype $paymenttype,
        \Veriteworks\Gmo\Model\Source\Splittype $splitcount,
        array $methodCodes = []
    ) {
        parent::__construct($ccConfig, $paymentHelper, $methodCodes);
        $this->config = $helper;
        $this->paymenttype = $paymenttype;
        $this->splitCount = $splitcount;
        $this->lists = $lists;
        if ($this->config->getRegisterCard()) {
            $this->cards = $this->loadCardInfo();
        }
    }
    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        //$config = parent::getConfig();
        $config = [
            'payment' => [
                \Veriteworks\Gmo\Model\Method\Cc::CODE => [
                    'gateway_url' => $this->config->getTokenUrl(),
                    'payment_type' => $this->getAvailablePaymentType(),
                    'can_use_split' => $this->canUseSplit(),
                    'split_count' => $this->getAvailableSplitCount(),
                    'shop_id' => $this->config->getShopId(),
                    'use_token' => $this->config->getUseToken(),
                    'registered_cards' => $this->cards,
                    'can_register_card' => $this->config->getRegisterCard(),
                    'can_use_registerd_card' => is_array($this->cards) ? count($this->cards) : 0,
                    'use_holder_name' => (int)$this->methods[self::CODE]->getConfigData('use_holder_name')
                ]
            ]
        ];

        return $config;
    }

    /**
     * Retrieve availables credit card types
     *
     * @param string $methodCode
     * @return array
     */
    private function getAvailablePaymentType()
    {
        $keys   = $this->paymenttype->toOptionArray();
        $availableTypes = $this->config->getPaymentType();
        $configData = [];

        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
        }

        foreach ($keys as $entry) {
            if (in_array($entry["value"], $availableTypes)) {
                $configData[$entry["value"]] = $entry["label"];
            }
        }

        return $configData;
    }

    /**
     * @return array
     */
    private function getAvailableSplitCount()
    {
        $keys   = $this->splitCount->toOptionArray();
        $availableTypes = $this->config->getSplitCount();
        $configData = [];

        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
        }

        foreach ($keys as $entry) {
            if (in_array($entry["value"], $availableTypes)) {
                $configData[$entry["value"]] = $entry["label"];
            }
        }

        return $configData;
    }

    /**
     * @return bool
     */
    private function canUseSplit()
    {
        $_config = explode(",", $this->config->getPaymentType());

        if (count($_config) == 1 && in_array('1', $_config)) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    private function loadCardInfo()
    {
        $list = $this->lists->loadRegisteredCards();
        $data = [];

        foreach ($list as $card) {
            $data[$card['customer_card_id']] =
                $card['card_number'] . " " . $card['card_valid_term'];
        }

        return $data;
    }
}
