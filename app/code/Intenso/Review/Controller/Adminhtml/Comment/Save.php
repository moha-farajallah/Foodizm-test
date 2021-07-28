<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Comment;

use Intenso\Review\Controller\Adminhtml\Comment as CommentController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends CommentController
{
    /**
     * Enable customer notification config path
     */
    const XML_PATH_SEND_CUSTOMER_NOTIFICATION = 'intenso_review/customer_email_options/enable_comment_notification';

    /**
     * Customer notification template config path
     */
    const XML_PATH_CUSTOMER_NOTIFICATION_TEMPLATE = 'intenso_review/customer_email_options/review_comment_template';

    /**
     * Review model factory
     *
     * @var \Intenso\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * Review data
     *
     * @var \Intenso\Review\Helper\Data
     */
    protected $reviewData = null;

    /**
     * Comment model factory
     *
     * @var \Intenso\Review\Model\CommentFactory
     */
    protected $commentFactory;

    /**
     * Akismet helper
     *
     * @var \Intenso\Review\Helper\Akismet
     */
    protected $akismetHelper = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $magentoReviewFactory;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Intenso\Review\Model\ReviewFactory $reviewFactory
     * @param \Intenso\Review\Model\CommentFactory $commentFactory
     * @param \Intenso\Review\Helper\Data $reviewData
     * @param \Intenso\Review\Helper\Akismet $akismetHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Review\Model\ReviewFactory $magentoReviewFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Intenso\Review\Model\ReviewFactory $reviewFactory,
        \Intenso\Review\Model\CommentFactory $commentFactory,
        \Intenso\Review\Helper\Data $reviewData,
        \Intenso\Review\Helper\Akismet $akismetHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Review\Model\ReviewFactory $magentoReviewFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->commentFactory = $commentFactory;
        $this->reviewData = $reviewData;
        $this->akismetHelper = $akismetHelper;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->magentoReviewFactory = $magentoReviewFactory;
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        parent::__construct($context, $reviewFactory, $commentFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (($data = $this->getRequest()->getPostValue()) && ($commentId = $this->getRequest()->getParam('id'))) {
            $comment = $this->commentFactory->create()->load($commentId);
            if (!$comment->getId()) {
                $this->messageManager->addError(__('The comment was removed by another user or does not exist.'));
            } else {
                try {
                    $customerFactory = $this->customerFactory->create();
                    $storeId = $this->storeManager->getStore()->getId();
                    $customerNotification = $this->scopeConfig->getValue(
                        self::XML_PATH_SEND_CUSTOMER_NOTIFICATION,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    );
                    // akismet - submit as spam
                    if ($comment->getStatusId() != \Intenso\Review\Model\Review::STATUS_SPAM &&
                        $data['status_id'] == \Intenso\Review\Model\Review::STATUS_SPAM) {
                        if ($comment->getCustomerId() > 0) {
                            $customer = $customerFactory->load($comment->getCustomerId());
                            $email = $customer->getEmail();
                        } else {
                            $email = $comment->getGuestEmail();
                        }
                        $akismetArray = [
                            'blog'                 => $this->storeManager->getStore()->getBaseUrl(),
                            'user_ip'              => $comment->getIp(),
                            'user_agent'           => $comment->getHttpUserAgent(),
                            'comment_type'         => 'comment',
                            'comment_author'       => $comment->getNickname(),
                            'comment_author_email' => $email,
                            'comment_content'      => $comment->getText()
                        ];
                        $this->akismetHelper->submitSpam($akismetArray);
                    }

                    $reviewFactory = $this->magentoReviewFactory->create()->load($comment->getReviewId());
                    $product = $this->productRepository->getById($reviewFactory->getEntityPkValue());

                    // Send email to customer
                    if (isset($data['status_id']) && $comment->getStatusId() != \Intenso\Review\Model\Review::STATUS_APPROVED &&
                        $data['status_id'] == \Intenso\Review\Model\Review::STATUS_APPROVED && $customerNotification) {
                        $reviewerEmail = false;
                        $customer = $customerFactory->load($reviewFactory->getCustomerId());
                        if ($reviewFactory->getCustomerId()) {
                            $reviewerEmail = $customer->getEmail();
                        } else {
                            $reviewSummary = $this->reviewFactory->create()->load($reviewFactory->getId());
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
                                ['comment' => $comment, 'store' => $this->storeManager->getStore()],
                                $reviewFactory->getStoreId()
                            );
                        }
                    }

                    $comment->addData($data)->save();
                    $this->reviewFactory->create()->syncReviews($comment->getReviewId());
                    $this->messageManager->addSuccess(__('You saved the comment.'));
                } catch (LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, $e->getMessage());
                }
            }

            $nextId = (int)$this->getRequest()->getParam('next_item');
            if ($nextId) {
                $resultRedirect->setPath('intenso_review/*/edit', ['id' => $nextId]);
            } else {
                $resultRedirect->setPath('*/*/');
            }
            return $resultRedirect;
        }
        $resultRedirect->setPath('intenso_review/*/');
        return $resultRedirect;
    }
}
