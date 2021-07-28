<?php
namespace Veriteworks\Gmo\Model\Method;

use \Magento\Payment\Model\Method\AbstractMethod;
use \Magento\Payment\Model\InfoInterface;
use \Magento\Sales\Model\Order\Payment\Transaction;
use \Magento\Sales\Model\Order\Payment\Transaction\ManagerInterface;
use \Magento\Sales\Api\Data\TransactionInterface;
use \Magento\Sales\Api\TransactionRepositoryInterface;
use \Magento\Framework\Exception\LocalizedException;
use \Magento\Backend\Model\Session\Quote as QuoteSession;

/**
 * Class Cvs
 * @package Veriteworks\Gmo\Model\Method
 */
class Cvs extends AbstractMethod
{
    /**
     *
     */
    const CODE = 'veritegmo_cvs';
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
    protected $_isInitializeNeeded = true;
    /**
     * @var bool
     */
    protected $_canAuthorize = true;
    /**
     * @var bool
     */
    protected $_canCapture = false;
    /**
     * @var bool
     */
    protected $_canCapturePartial = false;
    /**
     * @var bool
     */
    protected $_canRefund = false;
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
    protected $_canUseInternal = true;
    /**
     * @var bool
     */
    protected $_canUseCheckout = true;
    /**
     * @var bool
     */
    protected $_canReviewPayment = false;
    /**
     * @var bool
     */
    protected $_canUseForMultishipping = true;
    /**
     * @var bool
     */
    protected $_canSaveCc = false;
    /**
     * @var string
     */
    protected $_formBlockType = \Veriteworks\Gmo\Block\Form\Cvs::class;
    /**
     * @var string
     */
    protected $_infoBlockType = \Veriteworks\Gmo\Block\Info\Cvs::class;
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
     * @var TransactionRepositoryInterface
     */
    protected $_transactionRepository;

    /**
     * @var QuoteSession
     */
    private $quoteSession;

    /**
     * Cvs constructor.
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
     * @param \Magento\Sales\Api\TransactionRepositoryInterface $transactionRepository
     * @param QuoteSession $quoteSession
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
        TransactionRepositoryInterface $transactionRepository,
        QuoteSession $quoteSession,
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
        $this->_transactionRepository = $transactionRepository;
        $this->quoteSession = $quoteSession;
    }
    /**
     * @param \Magento\Framework\DataObject $data
     * @return $this
     */
    public function assignData(\Magento\Framework\DataObject $data)
    {
        if (!$data instanceof \Magento\Framework\DataObject) {
            $data = new \Magento\Framework\DataObject($data);
        }

        $additional = new \Magento\Framework\DataObject($data->getAdditionalData());

        $info = $this->getInfoInstance();
        $this->logger->debug([$additional->getCvsType()], [], true);
        $info->setCvsType($additional->getCvsType());
        $info->setAdditionalInformation('cvs_type', $additional->getCvsType());
        return $this;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initialize($paymentAction, $stateObject)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $this->getInfoInstance();
        $payment->setIsFraudDetected(false);
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $payment->setAmountAuthorized($order->getTotalDue());
        $payment->setBaseAmountAuthorized($order->getBaseTotalDue());
        $stateObject->setState(\Magento\Sales\Model\Order::STATE_NEW);
        $stateObject->setStatus(\Magento\Sales\Model\Order::STATE_PENDING_PAYMENT);
        $stateObject->setIsNotified(true);

        $this->order($payment, $order->getBaseGrandTotal());

        $payment->setSkipOrderProcessing(true);
    }

    /**
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function order(InfoInterface $payment, $amount)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $storeId = $this->getStore();

        $entry = $this->_connectorFactory->create();
        $entry->setCharset('sjis-win');
        $entry->setApiPath('EntryTranCvs');
        $entry->setParam('ShopID', $this->_gmoHelper->getShopId($storeId));
        $entry->setParam('ShopPass', $this->_gmoHelper->getShopPassword($storeId));
        $entry->setParam('OrderID', $order->getRealOrderId());
        $entry->setParam('Amount', round($amount, 0));

        $entry_response = $entry->execute();

        if (!$this->_handleResponse($entry_response)) {
            $incrementId = $this->_eavConfig
                ->getEntityType('order')
                ->fetchNewIncrementId($storeId);
            $order->setIncrementId($incrementId);
            $this->getQuote()->setReservedOrderId($incrementId);
            $this->order($payment, $amount);
        } else {
            $billing = $order->getBillingAddress();
            $fname = $this->trimName($billing->getFirstname());
            $lname = $this->trimName($billing->getLastname());

            $extAttributes = $billing->getExtensionAttributes();

            $fnamekana = $this->trimName($extAttributes->getFirstnamekana());
            $lnamekana = $this->trimName($extAttributes->getLastnamekana());

            $exec = $this->_connectorFactory->create();
            $exec->setApiPath('ExecTranCvs');
            $exec->setCharset('sjis-win');
            $exec->setParam('AccessID', $entry_response['AccessID']);
            $exec->setParam('AccessPass', $entry_response['AccessPass']);
            $exec->setParam('OrderID', $order->getRealOrderId());
            $exec->setParam('Convenience', $payment->getAdditionalInformation('cvs_type'));
            $exec->setParam('CustomerName', $lname . $fname);
            $exec->setParam('CustomerKana', $lnamekana . $fnamekana);
            $tel = str_replace(
                ["―","－", "－", "-"],
                "",
                mb_convert_kana($billing->getTelephone(), "rn")
            );

            $exec->setParam('TelNo', $tel);
            $exec->setParam('PaymentTermDay', $this->getConfigData('payment_term'));
            $exec->setParam('MailAddress', $order->getCustomerEmail());
            $exec->setParam('ShopMailAddress', $this->getConfigData('notify_email'));
            $exec->setParam('ReceiptsDisp11', $this->getConfigData('contact'));
            $exec->setParam('ReceiptsDisp12', $this->getConfigData('contact_tel'));
            $exec->setParam('ReceiptsDisp13', $this->getConfigData('contact_time'));

            $exec_response = $exec->execute();
//            $this->logger->debug($exec_response, [], true);
            $this->_handleResponse($exec_response);

            $result = array_merge($entry_response, $exec_response);
            $txnId = $order->getIncrementId() . '-order';

            $payment->setAmountAuthorized($order->getTotalDue());
            $payment->setBaseAmountAuthorized($order->getBaseTotalDue());
            $payment->setTransactionId($order->getIncrementId());
            $payment->setIsTransactionClosed(false);
            $payment->setIsTransactionPending(false);
            $payment->setTransactionAdditionalInfo(Transaction::RAW_DETAILS, $result);
            foreach ($result as $key => $value) {
                $payment->setAdditionalInformation($key, $value);
            }
            $transaction = $this->_transactionRepository->create()->setTxnId($txnId);
            $transaction
                ->setOrderPaymentObject($payment)
                ->setTxnType(Transaction::TYPE_AUTH)
                ->setAdditionalInformation(Transaction::RAW_DETAILS, $result)
                ->setIsClosed(false)
                ->isFailsafe(false);

            $payment->setCreatedTransaction($transaction)
                ->getOrder()->addRelatedObject($transaction);
        }

        return $this;
    }

    /**
     * @param $str
     * @return bool|string
     */
    private function trimName($str)
    {
        return mb_substr(preg_replace(
            '/ー―/u',
            '-',
            mb_convert_kana($str, 'S', "utf-8")
        ), 0, 20, 'utf-8');
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validate()
    {
        $info = $this->getInfoInstance();
        $errorMsg = false;

        // credit card acceptable currency
        $base_currency_code = $this->getQuote()->getBaseCurrencyCode();
        $store_currency_code = $this->getQuote()->getStoreCurrencyCode();

        if ($base_currency_code != 'JPY') {
            throw new LocalizedException(
                __(
                    'Unacceptable base currency code %1.',
                    $base_currency_code
                )
            );
        } elseif ($store_currency_code != 'JPY') {
            throw new LocalizedException(
                __(
                    'Unacceptable store currency code %1.',
                    $store_currency_code
                )
            );
        }

        $pattern = '/^(?:[\x00-\x7F\xA1-\xDF]|[\x89-\x97\x99-\x9F\xE0-\xE9]' .
                   '[\x40-\x7E\x80-\xFC]|\x81[\x40-\x7E\x80-\xAC\xB8-\xBF\xC8-\xCE\xDA-\xE8\xF0-\xF7\xFC]' .
                   '|\x82[\x4F-\x58\x60-\x79\x81-\x9A\x9F-\xF1]|\x83[\x40-\x7E\x80-\x96\x9F-\xB6\xBF-\xD6]' .
                   '|\x84[\x40-\x60\x70-\x7E\x80-\x91\x9F-\xBE]|\x88[\x9F-\xFC]|\x98[\x40-\x72\x9F-\xFC]' .
                   '|\xEA[\x40-\x7E\x80-\xA4])*$/';

        $vars = ['Lastname' => 10,
                      'Firstname' => 10];

        foreach ($vars as $_var => $_length) {
            $elemName = 'get' . $_var;
            $value = $this->getQuote()->getBillingAddress()->$elemName();
            if ($_var == 'Firstnamekana' || $_var == 'Lastnamekana') {
                $value = mb_convert_kana($value, "rnkh");
            }
            if (!preg_match($pattern, trim(mb_convert_encoding($value, 'SJIS-win', 'utf-8')))) {
                throw new LocalizedException(
                    __(
                        'Sorry your %1 contains illiegal characters. Please fix it and try again.',
                        $_var
                    )
                );
            }
        }

        return $this;
    }

    /**
     * @param InfoInterface $payment
     * @return $this|AbstractMethod
     * @throws LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    public function cancel(InfoInterface $payment)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $storeId = $order->getStoreId();
        if ($order->getData('no_cancel')) {
            return $this;
        }

        $transaction = $payment->getAuthorizationTransaction()->getAdditionalInformation(Transaction::RAW_DETAILS);
        $accessId = $transaction['AccessID'];
        $accessPass = $transaction['AccessPass'];

        $entry = $this->_connectorFactory->create();
        $entry->setCharset('sjis-win');
        $entry->setApiPath('CvsCancel');
        $entry->setParam('ShopID', $this->_gmoHelper->getShopId($storeId));
        $entry->setParam('ShopPass', $this->_gmoHelper->getShopPassword($storeId));
        $entry->setParam('OrderID', $order->getRealOrderId());
        $entry->setParam('AccessID', $accessId);
        $entry->setParam('AccessPass', $accessPass);

        $response = $entry->execute();
        $this->_handleResponse($response);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQuote()
    {
        if ($this->quoteSession->getCustomerId()) {
            return $this->quoteSession->getQuote();
        }
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Get checkout session namespace
     *
     * @return \Magento\Checkout\Model\Session
     */
    public function getCheckout()
    {
        if (!isset($this->_checkout)) {
            $this->_checkout = $this->_checkoutSession;
        }
        return $this->_checkout;
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
            } else {
                throw new LocalizedException(__(
                    'Payment process error.  Error code is %1 .',
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
        if (!in_array($currencyCode, ['JPY'])) {
            return false;
        }
        return true;
    }
}
