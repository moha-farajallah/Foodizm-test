<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Comment;

use Magento\Framework\Controller\ResultFactory;
use Intenso\Review\Controller\Adminhtml\Comment as CommentController;

class SaveStoreOwnerComment extends CommentController
{
    /**
     * Customer notification template config path
     */
    const XML_PATH_STORE_OWNER_REPLY_TEMPLATE = 'intenso_review/store_owner_comments/store_owner_reply_template';

    /**
     * @var \Intenso\Review\Model\StoreOwnerCommentFactory
     */
    protected $storeOwnerCommentFactory;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Review Summary model factory
     *
     * @var \Intenso\Review\Model\ReviewFactory
     */
    protected $reviewSummaryFactory;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $magentoReviewFactory;

    /**
     * Review data
     *
     * @var \Intenso\Review\Helper\Data
     */
    protected $reviewData = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Intenso\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\ReviewFactory $magentoReviewFactory
     * @param \Intenso\Review\Model\CommentFactory $commentFactory
     * @param \Intenso\Review\Model\StoreOwnerCommentFactory $storeOwnerCommentFactory
     * @param \Intenso\Review\Model\ReviewFactory $reviewSummaryFactory
     * @param \Intenso\Review\Helper\Data $reviewData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Registry $coreRegistry,
        \Intenso\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\ReviewFactory $magentoReviewFactory,
        \Intenso\Review\Model\CommentFactory $commentFactory,
        \Intenso\Review\Model\StoreOwnerCommentFactory $storeOwnerCommentFactory,
        \Intenso\Review\Model\ReviewFactory $reviewSummaryFactory,
        \Intenso\Review\Helper\Data $reviewData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->storeOwnerCommentFactory = $storeOwnerCommentFactory;
        $this->customerRepository = $customerRepository;
        $this->_coreRegistry = $coreRegistry;
        $this->reviewFactory = $reviewFactory;
        $this->magentoReviewFactory = $magentoReviewFactory;
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        $this->reviewData = $reviewData;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        parent::__construct($context, $reviewFactory, $commentFactory);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        $reviewId = (int) $this->getRequest()->getParam('review_id');
        $reviewFactory = $this->magentoReviewFactory->create()->load($reviewId);
        $responseContent = ['success' => false];

        if (!$reviewId || !$this->_formKeyValidator->validate($this->getRequest()) ||
            $reviewFactory->getStoreId() == \Magento\Store\Model\Store::DEFAULT_STORE_ID) {

            $resultJson->setData($responseContent);
            return $resultJson;
        }

        $comment = $this->storeOwnerCommentFactory->create()->load($reviewId, 'review_id');

        if ($this->getRequest()->getParam('remove') && $comment->getId()) {
            $comment->delete();

            $responseContent = ['success' => true];
            $resultJson->setData($responseContent);
            return $resultJson;
        }

        try {
            $customer = $this->customerRepository->getById($reviewFactory->getCustomerId());
            $email = $customer->getEmail();
            $name = $customer->getFirstname();
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $reviewSummary = $this->reviewSummaryFactory->create()->load($reviewId);
            $email = $reviewSummary->getGuestEmail();
            $name = $reviewFactory->getNickname();
        }

        if ($comment->getId()) {
            $comment->addData($this->getRequest()->getPostValue())->save();
        } else {
            $comment->setData($this->getRequest()->getPostValue())->save();
            $storeId = $this->storeManager->getStore()->getId();
            $product = $this->productRepository->getById($reviewFactory->getEntityPkValue());

            // send email to the customer
            $comment->setReviewerName($name);
            $comment->setReviewerEmail($email);
            $comment->setProductName($product->getName());
            $this->reviewData->sendMailToCustomer(
                $this->scopeConfig->getValue(
                    self::XML_PATH_STORE_OWNER_REPLY_TEMPLATE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                ),
                $email,
                ['comment' => $comment, 'store' => $this->storeManager->getStore()]
            );
        }

        $responseContent = ['success' => true];
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
