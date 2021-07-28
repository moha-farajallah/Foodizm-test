<?php
namespace Veriteworks\Gmo\Model\Config;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;
use Veriteworks\Gmo\Model\Method\Cvs;

/**
 * Class CvsProvider
 * @package Veriteworks\Gmo\Model\Config
 */
class CvsProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Cvs::CODE;

    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    protected $method;

    /**
     * @var \Veriteworks\Gmo\Model\Source\Cvstypes
     */
    protected $cvstypes;

    /**
     * CvsProvider constructor.
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Veriteworks\Gmo\Model\Source\Cvstypes $cvstypes
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Veriteworks\Gmo\Model\Source\Cvstypes $cvstypes
    ) {
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
        $this->cvstypes = $cvstypes;
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
                    'veritegmo_cvs' => [
                        'availableTypes' => $this->getCvsAvailableTypes(),
                    ],
                    'instructions' => [
                        Cvs::CODE => nl2br($this->method->getConfigData('instructions')),
                    ]
                ]
            ]);
        }

        return $config;
    }

    /**
     * Retrieve availables cvs types
     *
     * @param string $methodCode
     * @return array
     */
    protected function getCvsAvailableTypes()
    {
        $keys   = $this->cvstypes->toOptionArray();
        $availableTypes = $this->method->getConfigData('cvstypes');
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
}
