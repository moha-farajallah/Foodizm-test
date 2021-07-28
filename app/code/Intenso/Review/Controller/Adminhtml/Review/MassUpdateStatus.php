<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Review;

use Magento\Review\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

class MassUpdateStatus extends ProductController
{
    /**
     * Send review notification config path
     */
    const XML_PATH_REVIEW_NOTIFICATION = 'intenso_review/customer_email_options/enable_review_published_notification';

    /**
     * Review notification template config path
     */
    const XML_PATH_REVIEW_NOTIFICATION_TEMPLATE = 'intenso_review/customer_email_options/review_published_template';

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * Review Summary model factory
     *
     * @var \Intenso\Review\Model\ReviewFactory
     */
    protected $reviewSummaryFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Review data
     *
     * @var \Intenso\Review\Helper\Data
     */
    protected $reviewData = null;

    /**
     * Akismet helper
     *
     * @var \Intenso\Review\Helper\Akismet
     */
    protected $akismetHelper = null;

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
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Intenso\Review\Model\ReviewFactory $reviewSummaryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Intenso\Review\Helper\Data $reviewData
     * @param \Intenso\Review\Helper\Akismet $akismetHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Intenso\Review\Model\ReviewFactory $reviewSummaryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Intenso\Review\Helper\Data $reviewData,
        \Intenso\Review\Helper\Akismet $akismetHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerFactory = $customerFactory;
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        $this->storeManager = $storeManager;
        $this->reviewData = $reviewData;
        $this->akismetHelper = $akismetHelper;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        parent::__construct($context, $coreRegistry, $reviewFactory, $ratingFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $reviewsIds = $this->getRequest()->getParam('reviews');
        if (!is_array($reviewsIds)) {
            $this->messageManager->addError(__('Please select review(s).'));
        } else {
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($reviewsIds as $reviewId) {
                    $review = $this->reviewFactory->create()->load($reviewId);
                    $reviewSummary = $this->reviewSummaryFactory->create()->load($reviewId);
                    $storeId = $review->getStoreId();
                    $reviewNotification = $this->scopeConfig->getValue(
                        self::XML_PATH_REVIEW_NOTIFICATION,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    );

                    if ($review->getCustomerId() > 0) {
                        $customer = $this->customerFactory->create()->load($review->getCustomerId());
                        $email = $customer->getEmail();
                    } else {
                        $email = $reviewSummary->getGuestEmail();
                    }
                    // submit spam to Akismet API if status change to or from Spam
                    $akismetArray = [
                        'blog'                 => $this->storeManager->getStore()->getBaseUrl(),
                        'user_ip'              => $reviewSummary->getIp(),
                        'user_agent'           => $reviewSummary->getHttpUserAgent(),
                        'comment_type'         => 'comment',
                        'comment_author'       => $review->getNickname(),
                        'comment_author_email' => $email,
                        'comment_content'      => $review->getDetail()
                    ];
                    if ($review->getStatusId() != \Intenso\Review\Model\Review::STATUS_SPAM &&
                        $status == \Intenso\Review\Model\Review::STATUS_SPAM) {
                        $this->akismetHelper->submitSpam($akismetArray);
                    }
                    if ($review->getStatusId() == \Intenso\Review\Model\Review::STATUS_SPAM &&
                        $status != \Intenso\Review\Model\Review::STATUS_SPAM) {
                        $this->akismetHelper->submitHam($akismetArray);
                    }
                    // Send email to customer
                    if ($review->getStatusId() != \Intenso\Review\Model\Review::STATUS_APPROVED &&
                        $status == \Intenso\Review\Model\Review::STATUS_APPROVED && $reviewNotification) {
                        $product = $this->productRepository->getById($review->getEntityPkValue());
                        $review->setCustomerEmail($email);
                        $review->setUserIp($reviewSummary->getIp());
                        $review->setProductName($product->getName());
                        if($email != '') {
							$this->reviewData->sendMailToCustomer(
								$this->scopeConfig->getValue(
									self::XML_PATH_REVIEW_NOTIFICATION_TEMPLATE,
									\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
									$storeId
								),
								$email,
								['review' => $review, 'store' => $this->storeManager->getStore($storeId)],
								$storeId
							);
						}
                    }
                    $review->setStatusId($status)->save()->aggregate();
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($reviewsIds))
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while updating these review(s).')
                );
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('review/product/' . $this->getRequest()->getParam('ret', 'index'));
        return $resultRedirect;
    }
}
