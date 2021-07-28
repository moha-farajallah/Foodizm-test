<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Comment;

use Magento\Framework\Controller\ResultFactory;

class AddComment extends \Intenso\Review\Controller\Comment
{
    /**
     * Moderate guest comment config path
     */
    const XML_PATH_MODERATE_GUEST_COMMENT = 'intenso_review/configuration/moderate_guest_comment';

    /**
     * Moderate customer comment config path
     */
    const XML_PATH_MODERATE_CUSTOMER_COMMENT = 'intenso_review/configuration/moderate_customer_comment';

    /**
     * Enable customer notification config path
     */
    const XML_PATH_SEND_CUSTOMER_NOTIFICATION = 'intenso_review/customer_email_options/enable_comment_notification';

    /**
     * Customer notification template config path
     */
    const XML_PATH_CUSTOMER_NOTIFICATION_TEMPLATE = 'intenso_review/customer_email_options/review_comment_template';

    /**
     * Enable owner notification config path
     */
    const XML_PATH_SEND_OWNER_NOTIFICATION = 'intenso_review/owner_email_options/enable_new_comment_notification';

    /**
     * Owner notification template config path
     */
    const XML_PATH_OWNER_NOTIFICATION_TEMPLATE = 'intenso_review/owner_email_options/new_comment_template';

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Intenso\Review\Model\Comment
     */
    protected $commentFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

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
     * User data
     *
     * @var \Intenso\Review\Model\User
     */
    protected $userData;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

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
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Intenso\Review\Model\CommentFactory $commentFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Intenso\Review\Helper\Data $reviewData
     * @param \Intenso\Review\Helper\Akismet $akismetHelper
     * @param \Intenso\Review\Model\User $userData
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Intenso\Review\Model\ReviewFactory $reviewSummaryFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Intenso\Review\Model\CommentFactory $commentFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Intenso\Review\Helper\Data $reviewData,
        \Intenso\Review\Helper\Akismet $akismetHelper,
        \Intenso\Review\Model\User $userData,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Intenso\Review\Model\ReviewFactory $reviewSummaryFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->formKeyValidator = $formKeyValidator;
        $this->dateTime = $dateTime;
        $this->reviewFactory = $reviewFactory;
        $this->commentFactory = $commentFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->reviewData = $reviewData;
        $this->akismetHelper = $akismetHelper;
        $this->userData = $userData;
        $this->localeResolver = $localeResolver;
        $this->customerFactory = $customerFactory;
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        $this->productRepository = $productRepository;
        $this->backendUrl = $backendUrl;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Comments list
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $reviewId = $this->getRequest()->getParam('id', null);
        $responseContent = [
            'success' => false,
            'message' => __('Unable to post the comment.'),
        ];
        if ($reviewId && $this->getRequest()->isPost() && $this->formKeyValidator->validate($this->getRequest())) {
            $data = $this->getRequest()->getPostValue();
            $data['review_id'] = $reviewId;
            $comment = $this->commentFactory->create()->setData($data);
            if ($this->customerSession->isLoggedIn()) {
                $nickname = $this->customerSession->getCustomer()->getFirstname();
                $nickname .= ' ' . mb_strimwidth($this->customerSession->getCustomer()->getLastname(), 0, 2, '.');
                $comment->setNickname($nickname);
            }
            $validate = $comment->validate($this->customerSession->isLoggedIn());
            if ($validate === true) {
                try {
                    $storeId = $this->storeManager->getStore()->getId();
                    $status = \Intenso\Review\Model\Review::STATUS_PENDING;
                    $message = __('Thank you. Your comment has been submitted and is waiting for approval.');
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
                    $userIp = $this->userData->getRemoteAddress();
                    $userAgent = $this->userData->getHttpUserAgent();
                    $guestEmail = null;
                    if ($this->customerSession->getCustomerId()) {
                        $moderateCustomerComment = $this->scopeConfig->getValue(
                            self::XML_PATH_MODERATE_CUSTOMER_COMMENT,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $storeId
                        );
                        if (!$moderateCustomerComment) {
                            $status = \Intenso\Review\Model\Review::STATUS_APPROVED;
                            $message = __('Thank you. Your comment has been published.');
                        }
                    } else {
                        $nickname = $comment->getNickname();
                        $guestEmail = $comment->getEmail();
                        $moderateGuestComment = $this->scopeConfig->getValue(
                            self::XML_PATH_MODERATE_GUEST_COMMENT,
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                            $storeId
                        );
                        if (!$moderateGuestComment) {
                            $status = \Intenso\Review\Model\Review::STATUS_APPROVED;
                            $message = __('Thank you. Your comment has been published.');
                        }
                    }

                    // flag if comment is spam
                    $akismetArray = [
                        'blog'                 => $this->storeManager->getStore()->getBaseUrl(),
                        'user_ip'              => $userIp,
                        'user_agent'           => $userAgent,
                        'comment_type'         => 'comment',
                        'comment_author'       => $nickname,
                        'comment_author_email' => ($guestEmail) ? $guestEmail : $this->customerSession->getCustomer()->getEmail(),
                        'comment_content'      => $comment->getComment(),
                        'blog_lang'            => $this->localeResolver->getLocale()
                    ];
                    if ($this->akismetHelper->isSpam($akismetArray)) {
                        $status = \Intenso\Review\Model\Review::STATUS_SPAM;
                        $message = __('Thank you. Your comment has been submitted and is waiting for approval.');
                    }

                    // save
                    $comment->setReviewId($comment->getReviewId())
                        ->setCustomerId($this->customerSession->getCustomerId())
                        ->setNickname($nickname)
                        ->setText($comment->getComment())
                        ->setStatusId($status)
                        ->setGuestEmail($guestEmail)
                        ->setEmailSent(0)
                        ->setIp($userIp)
                        ->setHttpUserAgent($userAgent)
                        ->setCreatedAt($this->dateTime->formatDate(true))
                        ->save();

                    $reviewFactory = $this->reviewFactory->create()->load($comment->getReviewId());
                    $product = $this->productRepository->getById($reviewFactory->getEntityPkValue());

                    // Send email to customer
                    if ($status == \Intenso\Review\Model\Review::STATUS_APPROVED && $customerNotification) {
                        $customerFactory = $this->customerFactory->create()->load($reviewFactory->getCustomerId());
                        if ($reviewFactory->getCustomerId()) {
                            $reviewerEmail = $customerFactory->getEmail();
                        } else {
                            $reviewSummary = $this->reviewSummaryFactory->create()->load($reviewId);
                            $reviewerEmail = $reviewSummary->getGuestEmail();
                        }
                        if ($reviewerEmail) {
                            $comment->setReviewerEmail($reviewerEmail);
                            $comment->setReviewerNickname($reviewFactory->getNickname());
                            $comment->setProductName($product->getName());
                            $comment->setReviewUrl($this->storeManager->getStore()->getBaseUrl() .
                                'review/product/list/id/' . $reviewFactory->getEntityPkValue());
                            $this->reviewData->sendMailToCustomer(
                                $this->scopeConfig->getValue(
                                    self::XML_PATH_CUSTOMER_NOTIFICATION_TEMPLATE,
                                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                    $storeId
                                ),
                                $reviewerEmail,
                                ['comment' => $comment, 'store' => $this->storeManager->getStore($storeId)],
                                $storeId
                            );
                        }
                    }
                    // Send email to store owner
                    if ($status != \Intenso\Review\Model\Review::STATUS_SPAM && $ownerNotification) {
                        $comment->setProductName($product->getName());
                        $statuses = $this->reviewData->getReviewStatuses();
                        $status = isset($statuses[$status]) ? $statuses[$status] : '';
                        $comment->setStatus($status);
                        $comment->setCommentAuthorEmail(($guestEmail) ? $guestEmail : $this->customerSession->getCustomer()->getEmail());
                        $comment->setEditCommentUrl($this->backendUrl->getUrl('intenso_review/comment/edit', ['id' => $comment->getId()]));
                        $this->reviewData->sendMailToOwner(
                            $this->scopeConfig->getValue(
                                self::XML_PATH_OWNER_NOTIFICATION_TEMPLATE,
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                                $storeId
                            ),
                            ['comment' => $comment, 'store' => $this->storeManager->getStore()]
                        );
                    }

                    $responseContent = [
                        'success' => true,
                        'message' => $message,
                    ];
                } catch (\Exception $e) {
                    $responseContent = [
                        'success' => false,
                        'message' => __('Unable to post the comment.'),
                    ];
                }
            }
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);

        return $resultJson;
    }
}
