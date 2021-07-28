<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Product;

use Intenso\Review\Model\Review;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Controller\Product as ProductController;

class Post extends ProductController
{
    /**
     * Moderate guest review config path
     */
    const XML_PATH_MODERATE_GUEST_REVIEW = 'intenso_review/configuration/moderate_guest_review';

    /**
     * Moderate customer review config path
     */
    const XML_PATH_MODERATE_CUSTOMER_REVIEW = 'intenso_review/configuration/moderate_customer_review';

    /**
     * Enable customer notification config path
     */
    const XML_PATH_SEND_CUSTOMER_NOTIFICATION = 'intenso_review/customer_email_options/enable_review_published_notification';

    /**
     * Customer notification template config path
     */
    const XML_PATH_CUSTOMER_NOTIFICATION_TEMPLATE = 'intenso_review/customer_email_options/review_published_template';

    /**
     * Enable owner notification config path
     */
    const XML_PATH_SEND_OWNER_NOTIFICATION = 'intenso_review/owner_email_options/enable_new_review_notification';

    /**
     * Owner notification template config path
     */
    const XML_PATH_OWNER_NOTIFICATION_TEMPLATE = 'intenso_review/owner_email_options/new_review_template';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * User data
     *
     * @var \Intenso\Review\Model\User
     */
    protected $userData;

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
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @var \Intenso\Review\Model\Images\Processor
     */
    protected $imageProcessor;

    /**
     * @var \Intenso\Review\Model\ImageFactory
     */
    protected $imageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Intenso\Review\Model\User $userData
     * @param \Intenso\Review\Helper\Data $reviewData
     * @param \Intenso\Review\Helper\Akismet $akismetHelper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Intenso\Review\Model\Images\Processor $imageProcessor
     * @param \Intenso\Review\Model\ImageFactory $imageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Framework\Session\Generic $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Intenso\Review\Model\User $userData,
        \Intenso\Review\Helper\Data $reviewData,
        \Intenso\Review\Helper\Akismet $akismetHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Intenso\Review\Model\Images\Processor $imageProcessor,
        \Intenso\Review\Model\ImageFactory $imageFactory
    ) {
        parent::__construct(
            $context,
            $registry,
            $customerSession,
            $categoryRepository,
            $logger,
            $productRepository,
            $reviewFactory,
            $ratingFactory,
            $catalogDesign,
            $session,
            $storeManager,
            $formKeyValidator
        );
        $this->scopeConfig = $scopeConfig;
        $this->userData = $userData;
        $this->reviewData = $reviewData;
        $this->akismetHelper = $akismetHelper;
        $this->localeResolver = $localeResolver;
        $this->backendUrl = $backendUrl;
        $this->imageProcessor = $imageProcessor;
        $this->imageFactory = $imageFactory;
        $this->logger = $logger;
    }

    /**
     * Submit new review action
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $storeId = $this->storeManager->getStore()->getId();
        $status = Review::STATUS_PENDING;

        if (!$this->formKeyValidator->validate($this->getRequest())) {
            $responseContent = [
                'success' => false,
                'message' => __('Invalid session. Please reload the page and try again.'),
            ];
            $resultJson->setData($responseContent);
            return $resultJson;
        }

        $data = $this->reviewSession->getFormData(true);

        if ($data) {
            $rating = [];
            if (isset($data['ratings']) && is_array($data['ratings'])) {
                $rating = $data['ratings'];
            }
        } else {
            $data = $this->getRequest()->getPostValue();
            $rating = $this->getRequest()->getParam('ratings', []);
        }

        if (($product = $this->initProduct()) && !empty($data)) {
            /** @var \Magento\Review\Model\Review $review */
            $review = $this->reviewFactory->create()->setData($data);
            if (!$review->getNickname() && $this->customerSession->isLoggedIn()) {
                $nickname = $this->customerSession->getCustomer()->getFirstname();
                $nickname .= ' ' . mb_strimwidth($this->customerSession->getCustomer()->getLastname(), 0, 2, '.');
                $review->setNickname($nickname);
            }
            $review->unsetData('review_id');

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $moderateGuest = $this->scopeConfig->getValue(
                        self::XML_PATH_MODERATE_GUEST_REVIEW,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    );
                    $moderateCustomer = $this->scopeConfig->getValue(
                        self::XML_PATH_MODERATE_CUSTOMER_REVIEW,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    );
                    $customerNotification = $this->scopeConfig->getValue(
                        self::XML_PATH_SEND_CUSTOMER_NOTIFICATION,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    );
                    $ownerNotification = $this->scopeConfig->getValue(
                        self::XML_PATH_SEND_OWNER_NOTIFICATION,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    );
                    $email = ($review->getEmail()) ? $review->getEmail() : $this->customerSession->getCustomer()->getEmail();
                    $message = __('Thank you! You submitted your review for moderation.');

                    if (($this->customerSession->isLoggedIn() && !$moderateCustomer)
                        || (!$this->customerSession->isLoggedIn() && !$moderateGuest)) {
                        $status = Review::STATUS_APPROVED;
                        $message = __('Thank you! Your review has been published.');
                    }

                    $review->setUserIp($this->userData->getRemoteAddress());
                    $review->setUserAgent($this->userData->getHttpUserAgent());
                    // flag if comment is spam
                    $akismetArray = [
                        'blog'                 => $this->storeManager->getStore()->getBaseUrl(),
                        'user_ip'              => $review->getUserIp(),
                        'user_agent'           => $review->getUserAgent(),
                        'comment_type'         => 'comment',
                        'comment_author'       => $review->getNickname(),
                        'comment_author_email' => $email,
                        'comment_content'      => $review->getDetail(),
                        'blog_lang'            => $this->localeResolver->getLocale()
                    ];
                    if ($this->akismetHelper->isSpam($akismetArray)) {
                        $status = \Intenso\Review\Model\Review::STATUS_SPAM;
                        $message = __('Thank you! You submitted your review for moderation.');
                    }

                    $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($product->getId())
                        ->setStatusId($status)
                        ->setCustomerId($this->customerSession->getCustomerId())
                        ->setStoreId($this->storeManager->getStore()->getId())
                        ->setStores([$this->storeManager->getStore()->getId()])
                        ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        $this->ratingFactory->create()
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->setCustomerId($this->customerSession->getCustomerId())
                            ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();

                    // save review photos
                    if (isset($data['image'])) {
                        foreach ($data['image'] as $file) {
                            $renamedFile = $this->imageProcessor->duplicateImageFromTmp($file);
                            $this->imageFactory->create()
                                ->setReviewId($review->getId())
                                ->setFile($renamedFile)
                                ->save();
                        }
                    }

                    // Send email to customer
                    if ($status == Review::STATUS_APPROVED && $customerNotification) {
                        $review->setCustomerEmail($email);
                        $review->setProductName($product->getName());
                        $this->reviewData->sendMailToCustomer(
                            $this->scopeConfig->getValue(self::XML_PATH_CUSTOMER_NOTIFICATION_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
                            $email,
                            ['review' => $review, 'store' => $this->storeManager->getStore($storeId)],
                            $storeId
                        );
                    }

                    // Send email to store owner
                    if ($status != Review::STATUS_SPAM && $ownerNotification) {
                        $review->setCustomerEmail($email);
                        $review->setProductName($product->getName());
                        $statuses = $this->reviewData->getReviewStatuses();
                        $status = isset($statuses[$status]) ? $statuses[$status] : '';
                        $review->setStatus($status);
                        $review->setEditReviewUrl($this->backendUrl->getUrl('review/product/edit', ['id' => $review->getId()]));
                        $this->reviewData->sendMailToOwner(
                            $this->scopeConfig->getValue(self::XML_PATH_OWNER_NOTIFICATION_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
                            ['review' => $review, 'store' => $this->storeManager->getStore()]
                        );
                    }

                    $responseContent = [
                        'success' => true,
                        'message' => $message,
                    ];
                } catch (\Exception $e) {
                    $this->reviewSession->setFormData($data);
                    $responseContent = [
                        'success' => false,
                        'message' => __('We can\'t post your review right now.'),
                    ];
                }
            } else {
                $this->reviewSession->setFormData($data);
                $responseContent = [
                    'success' => false,
                    'message' => __('We can\'t post your review right now.'),
                ];
            }
        }
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
