<?php
namespace Veriteworks\Gmo\Controller\Mcp;

use \Magento\Framework\App\Action\Action;
use \Magento\Framework\App\CsrfAwareActionInterface;
use \Magento\Framework\App\Action\HttpPostActionInterface;
use \Magento\Framework\App\Request\InvalidRequestException;
use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Phrase;
use \Magento\Framework\Controller\Result\Redirect;
use \Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Multi currency error action
 */
class Error extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
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
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

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
        $this->scopeConfig = $scopeConfig;
        $this->session = $checkoutSession;
        $this->orderRepository = $orderRepository;
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
        /** @var \Veriteworks\Gmo\Model\Method\Cc $method */
        $method = $order->getPayment()->getMethodInstance();
        $this->coreRegistry->register('isSecureArea', true, true);

        if ($method->receive3d($this->_request, $order)) {
            $this->orderRepository->save($order);
            $this->_redirect('checkout/onepage/success');
        } else {
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
