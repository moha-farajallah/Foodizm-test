<?php
namespace Veriteworks\Gmo\Model\Config;

use Magento\Payment\Model\CcGenericConfigProvider;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\CcConfig;
use Veriteworks\Gmo\Model\Method\CcMulti;

/**
 * Class CcMultiProvider
 * @package Veriteworks\Gmo\Model\Config
 */
class CcMultiProvider extends CcGenericConfigProvider
{
    /**
     *
     */
    const CODE = CcMulti::CODE;

    /**
     * @var \Veriteworks\Gmo\Helper\Data
     */
    private $config;

    /**
     * ConfigProvider constructor.
     * @param \Magento\Payment\Model\CcConfig; $ccConfig
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Veriteworks\Gmo\Helper\Data $helper
     * @param array $methodCodes
     */
    public function __construct(
        CcConfig $ccConfig,
        PaymentHelper $paymentHelper,
        \Veriteworks\Gmo\Helper\Data $helper,
        array $methodCodes = []
    ) {
        parent::__construct($ccConfig, $paymentHelper, $methodCodes);
        $this->config = $helper;
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
                \Veriteworks\Gmo\Model\Method\CcMulti::CODE => [
                    'gateway_url' => $this->config->getTokenUrl(),
                    'shop_id' => $this->config->getShopId(),
                    'use_token' => $this->config->getMultiUseToken(),
                    'use_holder_name' => (int)$this->methods[self::CODE]->getConfigData('use_holder_name')
                ]
            ]
        ];

        return $config;
    }
}
