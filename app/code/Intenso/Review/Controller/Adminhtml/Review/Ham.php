<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Review;

use Magento\Framework\Controller\ResultFactory;
use Intenso\Review\Controller\Adminhtml\Review as ReviewController;

class Ham extends ReviewController
{
    /**
     * Review Summary model factory
     *
     * @var \Intenso\Review\Model\ReviewFactory
     */
    protected $reviewSummaryFactory;

    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Akismet helper
     *
     * @var \Intenso\Review\Helper\Akismet
     */
    protected $akismetHelper = null;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Intenso\Review\Model\ReviewFactory $reviewSummaryFactory
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Intenso\Review\Helper\Akismet $akismetHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Intenso\Review\Model\ReviewFactory $reviewSummaryFactory,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Intenso\Review\Helper\Akismet $akismetHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->reviewSummaryFactory = $reviewSummaryFactory;
        $this->reviewFactory = $reviewFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->akismetHelper = $akismetHelper;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        parent::__construct($context, $reviewSummaryFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $reviewId = $this->getRequest()->getParam('id', false);
        try {
            $review = $this->reviewFactory->create()->load($reviewId);
            $reviewSummary = $this->reviewSummaryFactory->create()->load($reviewId);
            if ($review->getCustomerId() > 0) {
                $customer = $this->customerFactory->create()->load($comment->getCustomerId());
                $email = $customer->getEmail();
            } else {
                $email = $reviewSummary->getGuestEmail();
            }
            $akismetArray = [
                'blog'                 => $this->storeManager->getStore()->getBaseUrl(),
                'user_ip'              => $reviewSummary->getIp(),
                'user_agent'           => $reviewSummary->getHttpUserAgent(),
                'comment_type'         => 'comment',
                'comment_author'       => $review->getNickname(),
                'comment_author_email' => $email,
                'comment_content'      => $review->getDetail()
            ];
            $this->akismetHelper->submitHam($akismetArray);
            $status = \Intenso\Review\Model\Review::STATUS_APPROVED;
            $review->setStatusId($status)->save();
            $reviewSummary->syncReviews($reviewId);
            $this->messageManager->addSuccess(__('The review has been marked as "Not Spam" and published.'));
            $resultRedirect->setPath('review/product/');
            return $resultRedirect;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while updating this review.'));
        }

        return $resultRedirect->setPath('review/product/edit/', ['id' => $reviewId]);
    }
}
