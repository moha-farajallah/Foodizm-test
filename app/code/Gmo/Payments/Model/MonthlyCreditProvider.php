<?php
namespace Gmo\Payments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Gmo\Payments\Model\Monthlycredit;

/**
 * Class MonthlyCreditProvider
 * @package Gmo\Payments\Model
 */
class MonthlyCreditProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Monthlycredit::CODE;

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
                        Monthlycredit::CODE => nl2br($this->method->getConfigData('instructions')),
                    ]
                ]
            ]);
        }

        return $config;
    }
}
