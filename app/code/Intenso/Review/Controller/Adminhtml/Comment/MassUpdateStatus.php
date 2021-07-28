<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Comment;

use Intenso\Review\Controller\Adminhtml\Comment as CommentController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;

class MassUpdateStatus extends CommentController
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
     * Comment model factory
     *
     * @var \Intenso\Review\Model\CommentFactory
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
     * Review data
     *
     * @var \Intenso\Review\Helper\Data
     */
    protected $reviewData = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Intenso\Review\Model\ReviewFactory $reviewFactory
     * @param \Intenso\Review\Model\CommentFactory $commentFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Review\Model\ReviewFactory $magentoReviewFactory
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Intenso\Review\Helper\Data $reviewData
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Intenso\Review\Model\ReviewFactory $reviewFactory,
        \Intenso\Review\Model\CommentFactory $commentFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Review\Model\ReviewFactory $magentoReviewFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Intenso\Review\Helper\Data $reviewData
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->commentFactory = $commentFactory;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->magentoReviewFactory = $magentoReviewFactory;
        $this->productRepository = $productRepository;
        $this->reviewData = $reviewData;
        parent::__construct($context, $reviewFactory, $commentFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $commentIds = $this->getRequest()->getParam('comments');
        if (!is_array($commentIds)) {
            $this->messageManager->addError(__('Please select comment(s).'));
        } else {
            try {
                $status = $this->getRequest()->getParam('status');

                foreach ($commentIds as $commentId) {
                    $comment = $this->commentFactory->create()->load($commentId);
                    $reviewFactory = $this->magentoReviewFactory->create()->load($comment->getReviewId());
                    $product = $this->productRepository->getById($reviewFactory->getEntityPkValue());
                    $storeId = $reviewFactory->getStoreId();
                    $customerNotification = $this->scopeConfig->getValue(
                        self::XML_PATH_SEND_CUSTOMER_NOTIFICATION,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $storeId
                    );

                    // Send email to customer
                    if ($comment->getStatusId() != \Intenso\Review\Model\Review::STATUS_APPROVED &&
                        $status == \Intenso\Review\Model\Review::STATUS_APPROVED && $customerNotification) {
                        $customer = $this->customerFactory->create()->load($reviewFactory->getCustomerId());
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
                                ['comment' => $comment, 'store' => $this->storeManager->getStore($storeId)],
                                $storeId
                            );
                        }
                    }

                    $comment->setStatusId($status)->save();
                    $this->reviewFactory->create()->syncReviews($comment->getReviewId());
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been updated.', count($commentIds))
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException(
                    $e,
                    __('Something went wrong while updating these comment(s).')
                );
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('intenso_review/*/' . $this->getRequest()->getParam('ret', 'index'));
        return $resultRedirect;
    }
}
