<?php
namespace Veriteworks\Gmo\Controller\Mcp;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\Controller\Result\Redirect;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\DataObject;
use Magento\Sales\Model\Order;

/**
 * Multi currency send action
 */
class Send extends Action
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $session;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * AbstractGmo constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->scopeConfig = $scopeConfig;
        $this->session = $checkoutSession;
        $this->coreRegistry = $coreRegistry;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * redirect action
     */
    public function execute()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->orderRepository->get($this->session->getLastOrderId());
        $this->logger->debug('LastOrderId ' .$this->session->getLastOrderId());
        $method = $order->getPayment()->getMethod();

        //check this transaction is 3D secure or not.
        if ($this->session->getCentinelUrl()) {
            $this->_view->loadLayout();
            $layout = $this->_view->getLayout();

            $block = $layout->getBlock('gmo_redirect');
            $this->logger->debug(get_class($block));
            $this->logger->debug($this->_request->getFullActionName());

            /** @var \Magento\Framework\DataObject $data */
            $data  = new DataObject();

            $data->setAccessId($this->session->getAccessId());
            $data->setToken($this->session->getToken());
            $this->logger->debug($method);

            $this->logger->debug(var_export($data->toArray(), true));
            $block->setMcpData($data);
            $block->setDestUrl($this->session->getCentinelUrl());

            $this->_view->renderLayout();
        } elseif ($order->getStatus() !== Order::STATE_PAYMENT_REVIEW) {
            /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('checkout/onepage/success');
        } else {
            $invoices = $order->getInvoiceCollection();
            foreach ($invoices as $invoice) {
                $invoice->delete();
            }
            $ordered_items = $order->getAllItems();
            foreach ($ordered_items as $item) {
                $item->setQtyInvoiced(0);
                $item->save();
            }
            $order->setStatus('new');
            $order->setState('pending');
            $this->coreRegistry->register('cancelling_review_order', true);
            $order->setCanSendCancelEmailFlag(false);
            $order->cancel();

            $quote = $this->quoteRepository->get($order->getQuoteId());
            $quote->setIsActive(1)->setReservedOrderId(null);
            $this->quoteRepository->save($quote);
            $this->session->replaceQuote($quote)->unsLastRealOrderId();

            $message = __(
                'Unable to place order. Please try again later.'
            );
            $this->messageManager->addErrorMessage($message);
            $this->_redirect('checkout/cart');
        }
    }
}
