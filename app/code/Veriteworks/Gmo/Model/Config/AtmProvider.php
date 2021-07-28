<?php
namespace Veriteworks\Gmo\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Veriteworks\Gmo\Model\Method\Atm;

/**
 * Class AtmProvider
 * @package Veriteworks\Gmo\Model\Config
 */
class AtmProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Atm::CODE;

    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $method;

    /**
     * AtmProvider constructor.
     * @param \Magento\Payment\Helper\Data $paymentHelper
     */
    public function __construct(
        PaymentHelper $paymentHelper
    ) {
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $config = [];
        $methodCode = $this->methodCode;

        if ($this->method->isAvailable()) {
            $config = array_merge_recursive($config, [
                'payment' => [
                    'instructions' => [
                        Atm::CODE => nl2br($this->method->getConfigData('instructions')),
                    ]
                ]
            ]);
        }

        return $config;
    }
}
