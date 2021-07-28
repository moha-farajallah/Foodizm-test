<?php
namespace Veriteworks\Gmo\Controller\Notify;

use \Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpPostActionInterface;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Phrase;
use \Magento\Store\Model\ScopeInterface;
use \Magento\Sales\Model\Order;
use \Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Api\OrderManagementInterface as OrderManagement;

/**
 * Notification receive action
 */
class Receive extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Receive constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param InvoiceSender $invoiceSender
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        InvoiceSender $invoiceSender,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->orderFactory = $orderFactory;
        $this->scopeConfig = $scopeConfig;
        $this->session = $checkoutSession;
        $this->coreRegistry = $coreRegistry;
        $this->invoiceSender = $invoiceSender;
        $this->logger = $logger;
    }

    /**
     * receive payment notify action
     */
    public function execute()
    {
        if (!$this->_request->isPost()) {
            $this->_response->setBody('1');
            return;
        }

        $payType = $this->_request->getParam('PayType', null);
        $shopId = $this->_request->getParam('ShopID', null);
        $shopPass = $this->_request->getParam('ShopPass', null);
        $orderId = $this->_request->getParam('OrderID', null);
        $status = $this->_request->getParam('Status', null);
        $this->logger->info(var_export($this->_request->getParams(), true));

        if (!$this->chckShopID($shopId, $shopPass)) {
            $this->_response->setBody('1');
            return;
        }

        if ($orderId === null) {
            if (in_array($status, ['REQSUCCESS', 'AUTH', 'AUTHENTICATED'])) {
                $this->_response->setBody('0');
            } else {
                $this->logger->critical('No order id was given.');
                $this->_response->setBody('1');
            }
        }

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderFactory->create()->loadByIncrementId($orderId);
        $this->logger->info('OrderId ' . $orderId);

        if (!$order->getId()) {
            $this->logger->info(__('No such order id %1.', $orderId));
            $this->_response->setBody('0');
            return;
        }

        //$method = $order->getPayment()->getMethodInstance();
        $this->coreRegistry->register('isSecureArea', true, true);

        $state = $order->getState();
        $shouldReHold = false;

        if ($state == Order::STATE_HOLDED && $order->canUnhold()) {
            $order->unhold();
            $this->orderRepository->save($order);
            $shouldReHold = true;
        }

        if ($order->getState() === Order::STATE_NEW) {
            if ($status === "REQSUCCESS") {
                $order->addCommentToStatusHistory(
                    __('Order was accepted successfully.'),
                    Order::STATE_PENDING_PAYMENT
                );
                $this->orderRepository->save($order);
            } elseif ($status === "PAYSUCCESS" && $order->canInvoice()) {
                $invoice = $order->prepareInvoice();
                $invoice->register()->pay();
                $order->addRelatedObject($invoice);
                $orderState = Order::STATE_PROCESSING;
                $order->setState($orderState);
                $order->addCommentToStatusHistory(
                    __('Order was payed successfully.'),
                    $orderState
                );
                $invoice->setSendEmail(true);
                $this->orderRepository->save($order);
                $this->invoiceSender->send($invoice);
            } elseif ($status === "PAYFAIL" || $status === "EXPIRED") {
                $order->setData('no_cancel', true);
                $order->cancel();
                $order->addCommentToStatusHistory(
                    __('Order was expired or failed. ErrorCode:' . $status . '.'),
                    Order::STATE_CANCELED
                );
                $this->orderRepository->save($order);
            }
        }

        if ($shouldReHold === true && $order->canHold()) {
            $order->hold();
            $this->orderRepository->save($order);
        }

        $this->_response->setBody('0');
    }

    /**
     * @param $shopId
     * @param $shopPass
     * @return bool
     */
    private function chckShopID($shopId, $shopPass)
    {
        $confShopId = $this->getConfig('shop_id');
        if ($shopId == $confShopId && $shopPass == '**********') {
            return true;
        }

        return false;
    }

    /**
     * @param $key
     * @return mixed
     */
    private function getConfig($key)
    {
        return $this->scopeConfig->getValue(
            'veritegmo/common/' . $key,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setRefererOrBaseUrl();

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
