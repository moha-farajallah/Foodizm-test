<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Review;

use Intenso\Review\Model\Review;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Review\Controller\Adminhtml\Product as ProductController;

class Save extends ProductController
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
     * @var \Magento\Review\Model\Rating\Option\Vote
     */
    protected $ratingOptionVote;

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
     * @param \Magento\Review\Model\Rating\Option\Vote $ratingOptionVote
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
        \Magento\Review\Model\Rating\Option\Vote $ratingOptionVote,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerFactory = $customerFactory;
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        $this->storeManager = $storeManager;
        $this->reviewData = $reviewData;
        $this->akismetHelper = $akismetHelper;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
        $this->ratingOptionVote = $ratingOptionVote;
        $this->logger = $logger;
        parent::__construct($context, $coreRegistry, $reviewFactory, $ratingFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (($data = $this->getRequest()->getPostValue()) && ($reviewId = $this->getRequest()->getParam('id'))) {
            $review = $this->reviewFactory->create()->load($reviewId);
            if (!$review->getId()) {
                $this->messageManager->addError(__('The review was removed by another user or does not exist.'));
            } else {
                try {
                    $storeId = $review->getStoreId();
                    $reviewSummary = $this->reviewSummaryFactory->create()->load($reviewId);
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
                    // submit spam to Akismet API if status change to Spam
                    if ($review->getStatusId() != Review::STATUS_SPAM &&
                        $data['status_id'] == Review::STATUS_SPAM) {
                        $akismetArray = [
                            'blog'                 => $this->storeManager->getStore()->getBaseUrl(),
                            'user_ip'              => $reviewSummary->getIp(),
                            'user_agent'           => $reviewSummary->getHttpUserAgent(),
                            'comment_type'         => 'comment',
                            'comment_author'       => $review->getNickname(),
                            'comment_author_email' => $email,
                            'comment_content'      => $review->getDetail()
                        ];
                        $this->akismetHelper->submitSpam($akismetArray);
                    }

                    // Send email to customer
                    if (isset($data['status_id']) && $review->getStatusId() != Review::STATUS_APPROVED &&
                        $data['status_id'] == Review::STATUS_APPROVED && $reviewNotification) {
                        $product = $this->productRepository->getById($review->getEntityPkValue());
                        $review->setCustomerEmail($email);
                        $review->setUserIp($reviewSummary->getIp());
                        $review->setProductName($product->getName());
                        $this->reviewData->sendMailToCustomer(
                            $this->scopeConfig->getValue(
                                self::XML_PATH_REVIEW_NOTIFICATION_TEMPLATE,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                $storeId
                            ),
                            $email,
                            ['review' => $review, 'store' => $this->storeManager->getStore()],
                            $storeId
                        );
                    }

                    $review->addData($data)->save();

                    $arrRatingId = $this->getRequest()->getParam('ratings', []);
                    /** @var \Magento\Review\Model\Rating\Option\Vote $votes */
                    $votes = $this->ratingOptionVote
                        ->getResourceCollection()
                        ->setReviewFilter($reviewId)
                        ->addOptionInfo()
                        ->load()
                        ->addRatingOptions();
                    foreach ($arrRatingId as $ratingId => $optionId) {
                        if ($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                            $this->ratingFactory->create()
                                ->setVoteId($vote->getId())
                                ->setReviewId($review->getId())
                                ->updateOptionVote($optionId);
                        } else {
                            $this->ratingFactory->create()
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->addOptionVote($optionId, $review->getEntityPkValue());
                        }
                    }

                    $review->aggregate();

                    $this->_eventManager->dispatch('adminhtml_intenso_review_save');

                    $this->messageManager->addSuccess(__('You saved the review.'));
                } catch (LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving this review.'));
                }
            }

            $nextId = (int)$this->getRequest()->getParam('next_item');
            if ($nextId) {
                $resultRedirect->setPath('review/product/edit', ['id' => $nextId]);
            } elseif ($this->getRequest()->getParam('ret') == 'pending') {
                $resultRedirect->setPath('review/product/pending');
            } else {
                $resultRedirect->setPath('review/product/');
            }
            return $resultRedirect;
        }
        $resultRedirect->setPath('review/product/');
        return $resultRedirect;
    }
}
