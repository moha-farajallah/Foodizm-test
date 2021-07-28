<?php
namespace Veriteworks\Gmo\Model\Method;

use Magento\Payment\Model\InfoInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Inspection\Exception;
use Magento\Sales\Model\ResourceModel\Order\Payment as OrderPaymentResource;

/**
 * Class CcMulti
 * @package Veriteworks\Gmo\Model\Method
 */
class CcMulti extends \Magento\Payment\Model\Method\Cc
{
    /**
     *
     */
    const CODE = 'veritegmo_ccmulti';
    /**
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * @var bool
     */
    protected $_isGateway = true;
    /**
     * @var bool
     */
    protected $_canAuthorize = true;
    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canRefund = true;
    /**
     * @var bool
     */
    protected $_canCancel = true;
    /**
     * @var bool
     */
    protected $_canVoid = true;
    /**
     * @var bool
     */
    protected $_canUseCheckout = true;
    /**
     * @var string
     */
    protected $_formBlockType = \Veriteworks\Gmo\Block\Form\Cc::class;
    /**
     * @var string
     */
    protected $_infoBlockType = \Veriteworks\Gmo\Block\Info\Cc::class;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Veriteworks\Gmo\Gateway\ConnectorFactory
     */
    protected $_connectorFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Veriteworks\Gmo\Helper\Data
     */
    protected $_gmoHelper;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var mixed|null
     */
    protected $_minAmount = null;
    /**
     * @var mixed|null
     */
    protected $_maxAmount = null;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    protected $_orderSender;

    /**
     * @var OrderPaymentResource
     */
    protected $orderPaymentResource;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * CcMulti constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Veriteworks\Gmo\Gateway\ConnectorFactory $connectorFactory
     * @param \Veriteworks\Gmo\Helper\Data $gmoHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerCustomerFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender
     * @param \Magento\Sales\Model\ResourceModel\Order\Payment $orderPaymentResource
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\RequestInterface $request,
        \Veriteworks\Gmo\Gateway\ConnectorFactory $connectorFactory,
        \Veriteworks\Gmo\Helper\Data $gmoHelper,
        \Magento\Customer\Model\CustomerFactory $customerCustomerFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        OrderPaymentResource $orderPaymentResource,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $moduleList,
            $localeDate,
            null,
            null,
            $data
        );

        $this->_connectorFactory = $connectorFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_gmoHelper = $gmoHelper;
        $this->_request = $request;
        $this->_eavConfig = $eavConfig;
        $this->_customerCustomerFactory = $customerCustomerFactory;
        $this->_minAmount = $this->getConfigData('min_order_total');
        $this->_maxAmount = $this->getConfigData('max_order_total');
        $this->_orderSender = $orderSender;
        $this->_urlBuilder = $urlBuilder;
        $this->_orderPaymentResource = $orderPaymentResource;
    }

    /**
     * @param \Magento\Framework\DataObject $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        if (!$data instanceof \Magento\Framework\DataObject) {
            $data = new \Magento\Framework\DataObject($data);
        }

        $additional = new \Magento\Framework\DataObject($data->getAdditionalData());

        $info = $this->getInfoInstance();
        $info->setCcType(
            $additional->getCcType()
        )->setCcOwner(
            $additional->getCcOwner()
        )->setCcLast4(
            substr($additional->getCcNumber(), -4)
        )->setCcNumber(
            $additional->getCcNumber()
        )->setCcCid(
            $additional->getCcCid()
        )->setCcExpMonth(
            $additional->getCcExpMonth()
        )->setCcExpYear(
            $additional->getCcExpYear()
        );

        $info->setCcToken($additional->getCcToken());
        $info->setAdditionalInformation('cc_token', $additional->getCcToken());

        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        $info = $this->getInfoInstance();
        $payment->setIsFraudDetected(false);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $info->getOrder();
        $storeId = $this->getStore();
        $checkout = $this->_checkoutSession;
        $useCard = $info->getAdditionalInformation('use_card');

        $entry = $this->_connectorFactory->create();
        $entry->setApiPath('EntryTranMcp');
        $entry->setParam('ShopID', $this->_gmoHelper->getShopId($storeId));
        $entry->setParam('ShopPass', $this->_gmoHelper->getShopPassword($storeId));
        $entry->setParam('OrderID', $order->getRealOrderId());
        if ($this->getConfigData('payment_action', $storeId) == 'authorize_capture') {
            $entry->setParam('JobCd', 'CAPTURE');
        } else {
            $entry->setParam('JobCd', 'AUTH');
        }
        $entry->setParam('Currency', $order->getBaseCurrencyCode());
        $entry->setParam('Amount', sprintf('%01.2f', $order->getBaseGrandTotal()));
        $entry->setParam('ItemCode', '0000990');

        $entry->setParam('TdFlag', $this->getConfigData('use_3dsecure', $storeId));

        if ($this->getConfigData('use_3dsecure', $storeId)) {
            $entry->setParam('TdTenantName', $this->getConfigData('tenant_name', $storeId));
        }

        $entry_response = $entry->execute();

        if (!$this->_handleResponse($entry_response)) {
            $incrementId = $this->_eavConfig
                ->getEntityType('order')
                ->fetchNewIncrementId($storeId);
            $order->setIncrementId($incrementId);
            $this->getQuote()->setReservedOrderId($incrementId);
            $this->authorize($payment, $amount);
        } else {
            $exec = $this->_connectorFactory->create();
            $exec->setApiPath('ExecTranMcp');
            $exec->setParam('ShopID', $this->_gmoHelper->getShopId($storeId));
            $exec->setParam('ShopPass', $this->_gmoHelper->getShopPassword($storeId));

            if (!$this->_gmoHelper->getMultiUseToken()) {
                $exec->setParam('CardNo', $info->getCcNumber());
                $exec->setParam(
                    'Expire',
                    substr($info->getCcExpYear(), -2) . sprintf(
                        "%02d",
                        $info->getCcExpMonth()
                    )
                );
                $exec->setParam('SecurityCode', $info->getCcCid());
            } else {
                $exec->setParam('Token', $info->getAdditionalInformation('cc_token'));
            }

            if ($this->getConfigData('use_3dsecure')) {
                $exec->setParam('HttpAccept', $this->_request->getServer('HTTP_ACCEPT'));
                $exec->setParam(
                    'HttpUserAgent',
                    $this->_request->getServer('HTTP_USER_AGENT')
                );
                $exec->setParam('DeviceCategory', '0');
            }

            $exec->setParam('AccessID', $entry_response['AccessID']);
            $exec->setParam('AccessPass', $entry_response['AccessPass']);
            $exec->setParam('RetURL', $this->_urlBuilder->getUrl(
                'gmo/mcp/receive',
                ['_secure' => true]
            ));
            $exec->setParam('ErrorRcvURL', $this->_urlBuilder->getUrl(
                'gmo/mcp/error',
                ['_secure' => true]
            ));
            $exec->setParam('OrderID', $order->getRealOrderId());

            $exec_response = $exec->execute();
            $this->_handleResponse($exec_response);

            if (array_key_exists(
                'ACS',
                $exec_response
            ) && $exec_response['ACS'] == '1'
            ) {
                $checkout->setCentinelUrl($exec_response['StartURL']);
                $checkout->setToken($exec_response['Token']);
                $checkout->setAccessId($exec_response['AccessID']);

                $payment->setCcTransId($exec_response['AccessID']);
                $payment->setTransactionId($order->getRealOrderId());
                $payment->setIsTransactionClosed(false);
                $payment->setIsTransactionPending(true);
                $result = array_merge($entry_response, $exec_response);
                $payment->setTransactionAdditionalInfo(Transaction::RAW_DETAILS, $result);

                $order->setCanSendNewEmailFlag(false);
            } else {
                $this->setCentinelUrl(null);
                $checkout->setPareq(null);
                $checkout->setMd(null);

                $payment->setCcTransId($exec_response['AccessID']);
                $payment->setTransactionId($order->getRealOrderId());
                $payment->setIsTransactionClosed(false);
                $result = array_merge($entry_response, $exec_response);
                $payment->setTransactionAdditionalInfo(Transaction::RAW_DETAILS, $result);
            }
        }
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $storeId = $this->getStore();
        if ($this->getConfigData('payment_action', $storeId) == 'authorize_capture') {
            return $this->authorize($payment, $amount);
        }

        $transaction = $payment->getAuthorizationTransaction()->getAdditionalInformation(Transaction::RAW_DETAILS);
        $accessId = $transaction['AccessID'];
        $accessPass = $transaction['AccessPass'];

        $obj = $this->_connectorFactory->create();
        $obj->setApiPath('McpSales');
        $obj->setParam('ShopID', $this->_gmoHelper->getShopId($storeId));
        $obj->setParam('ShopPass', $this->_gmoHelper->getShopPassword($storeId));
        $obj->setParam('AccessID', $accessId);
        $obj->setParam('AccessPass', $accessPass);

        $response = $obj->execute();
        $this->_handleResponse($response);

        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \Veriteworks\Gmo\Model\Method\CcMulti|void
     */
    public function cancel(\Magento\Payment\Model\InfoInterface $payment)
    {
        $order = $payment->getOrder();
        $storeId = $this->getStore();

        if ($order->getStatus() == Order::STATE_PAYMENT_REVIEW) {
            return;
        }

        $transaction = $payment->getAuthorizationTransaction()->getAdditionalInformation(Transaction::RAW_DETAILS);
        $accessId = $transaction['AccessID'];
        $accessPass = $transaction['AccessPass'];

        $obj = $this->_connectorFactory->create();
        $obj->setApiPath('McpCancel');
        $obj->setParam('ShopID', $this->_gmoHelper->getShopId($storeId));
        $obj->setParam('ShopPass', $this->_gmoHelper->getShopPassword($storeId));
        $obj->setParam('AccessID', $accessId);
        $obj->setParam('AccessPass', $accessPass);

        $response = $obj->execute();
        $this->_handleResponse($response);

        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return \Veriteworks\Gmo\Model\Method\CcMulti
     */
    public function refund(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $order = $payment->getOrder();
        $storeId = $this->getStore();
        $transaction = $payment->getAuthorizationTransaction()->getParentTransaction();
        $txn = $transaction->getAdditionalInformation(Transaction::RAW_DETAILS);
        $accessId = $txn['AccessID'];
        $accessPass = $txn['AccessPass'];

        $obj = $this->_connectorFactory->create();
        $obj->setParam('ShopID', $this->_gmoHelper->getShopId($storeId));
        $obj->setParam('ShopPass', $this->_gmoHelper->getShopPassword($storeId));
        $obj->setApiPath('McpCancel');
        $obj->setParam('AccessID', $accessId);
        $obj->setParam('AccessPass', $accessPass);

        $response = $obj->execute();
        $this->_handleResponse($response);

        $order->setState(
            Order::STATE_PROCESSING,
            true,
            __('Order refund success.'),
            true
        );
        $order->save();
        return $this;
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @return \Veriteworks\Gmo\Model\Method\CcMulti
     */
    public function void(\Magento\Payment\Model\InfoInterface $payment)
    {
        return $this->cancel($payment);
    }

    /**
     * @return $this|bool
     */
    public function validate()
    {
        return true;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function receive3d(RequestInterface $request, Order $order)
    {
        $params = $request->getParams();
        $storeId = $order->getStoreId();
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $order->getPayment();
        $accessId   = $request->getParam('AccessID', $storeId);
        $status      = $request->getParam('Status', $storeId);

        try {
            $this->_handleResponse($params);

            if ($status != 'PAYFAIL') {
                $payment->setTransactionId($order->getRealOrderId() . '-authorize');
                $payment->setIsTransactionClosed(false);
                $payment->setIsTransactionPending(false);
                $payment->setTransactionAdditionalInfo('AccessID', $accessId);
                $payment->setTransactionAdditionalInfo('Status', $status);
                $this->orderPaymentResource->save($payment);

                $order->setState(Order::STATE_PROCESSING)
                    ->addStatusToHistory(
                        true,
                        __('3D Secure authorization success.'),
                        false
                    );

                if ($order->getCanSendNewEmailFlag()) {
                    $this->_orderSender->send($order);
                }
            } else {
                throw new LocalizedException(__(
                    '3D Secure Authorization process error.  Error code is %1 .',
                    preg_replace('/\|/', ',', $params['ErrInfo'])
                ));
            }

            return true;
        } catch (\Exception $e) {
//            $this->logger->debug($params, [], true);
            $this->logger->debug([$e->getMessage()], [], true);
            return false;
        }
    }

    /**
     * @param $response
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _handleResponse($response)
    {
        if (array_key_exists(
            'ErrCode',
            $response
        ) && $response['ErrCode'] == 'network error'
        ) {
            throw new LocalizedException(__('Could not access payment gateway server. Please retry again.'));
        } elseif (array_key_exists('ErrCode', $response)) {
            if (array_key_exists(
                'ErrInfo',
                $response
            ) && ($this->_gmoHelper->isRetryNeeded($response))
            ) {
                return false;
            } elseif (array_key_exists('ErrInfo', $response) && ($response['ErrInfo'] != '')) {
                throw new LocalizedException(__(
                    'Authorization process error.  Error code is %1 .',
                    preg_replace('/\|/', ',', $response['ErrInfo'])
                ));
            }
        }
        return true;
    }

    /**
     * @param $response
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _handleRegisterResponse($response)
    {
        if (array_key_exists(
            'ErrCode',
            $response
        ) && $response['ErrCode'] == 'network error'
        ) {
            throw new LocalizedException(__('Could not access payment gateway server. Please retry again.'));
        } elseif (array_key_exists('ErrCode', $response)) {
            if (array_key_exists(
                'ErrInfo',
                $response
            ) && ($this->_gmoHelper->isIgnore($response))
            ) {
                return false;
            } else {
                throw new LocalizedException(__(
                    'Authorization process error.  Error code is %1 .',
                    preg_replace('/\|/', ',', $response['ErrInfo'])
                ));
            }
        }
        return true;
    }

    /**
     * Check method for processing with base currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->getAcceptedCurrencyCodes())) {
            return false;
        }
        return true;
    }

    /**
     * Return array of currency codes supplied by Payment Gateway
     *
     * @return array
     */
    public function getAcceptedCurrencyCodes()
    {
        $codes = explode(',', $this->getConfigData('currency'));
        return $codes;
    }

    /**
     * @return mixed
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }
}
