<?php
namespace Veriteworks\Gmo\Block\Form;

use \Magento\Framework\View\Element\Template;
use \Veriteworks\Gmo\Model\Source\Cvstypes;
use \Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Cvs payment method form
 */
class Cvs extends \Magento\Payment\Block\Form
{
    /**
     * method code
     */
    const CODE = 'veritegmo_cvs';
    /**
     * @var string
     */
    protected $_template = 'Veriteworks_Gmo::form/cvs.phtml';

    /**
     * @var Cvstypes
     */
    private $cvsTypes;

    /**
     * @var \Magento\Payment\Model\MethodInterface
     */
    private $method;

    /**
     * Cvs constructor.
     * @param Template\Context $context
     * @param Cvstypes $cvstypes
     * @param PaymentHelper $paymentHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Cvstypes $cvstypes,
        PaymentHelper $paymentHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->method = $paymentHelper->getMethodInstance(self::CODE);
        $this->cvsTypes = $cvstypes;
    }

    /**
     * @return array
     */
    public function getCvsAvailableTypes()
    {
        $keys   = $this->cvsTypes->toOptionArray();
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
