<?php
namespace Veriteworks\Gmo\Block\Card;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Store\Model\ScopeInterface;

/**
 * Card edit block
 */
class Edit extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $paymentConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Payment\Model\Config $paymentConfig,
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->currentCustomer = $currentCustomer;
        $this->paymentConfig = $paymentConfig;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set(__('Add new card'));
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        if ($this->getRefererUrl()) {
            return $this->getRefererUrl();
        }
        return $this->getUrl('customer/account/', ['_secure' => true]);
    }

    /**
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        $customer = $this->getData('customer');
        if ($customer === null) {
            try {
                $id = $this->currentCustomer->getCustomerId();
                $customer = $this->customerRepository->getById($id);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                return null;
            }
            $this->setData('customer', $customer);
        }
        return $customer;
    }

    /**
     * Retrieve availables credit card types
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function getCcAvailableTypes()
    {
        $types = $this->paymentConfig->getCcTypes();

        $availableTypes = $this->getConfig('cctypes');
        if ($availableTypes) {
            $availableTypes = explode(',', $availableTypes);
            foreach ($types as $code => $name) {
                if (!in_array($code, $availableTypes)) {
                    unset($types[$code]);
                }
            }
        }

        return $types;
    }

    /**
     * Retrieve credit card expire months
     *
     * @return array
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if ($months === null) {
            $months[0] = __('Month');
            $months = array_merge($months, $this->paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Retrieve credit card expire years
     *
     * @return array
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if ($years === null) {
            $years = $this->paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Retrive Gmo Gateway Url
     */
    public function getGatewayUrl()
    {
        return $this->_scopeConfig->
        getValue('veritegmo/common/gateway_url', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getShopId()
    {
        return $this->_scopeConfig->
        getValue('veritegmo/common/shop_id', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getUseToken()
    {
        return $this->getConfig('use_token');
    }

    /**
     * @param $key
     * @return mixed
     */
    private function getConfig($key)
    {
        $key = 'payment/veritegmo_cc/' . $key;
        return $this->_scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE);
    }
}
