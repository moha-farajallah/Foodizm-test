<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Comment;

use Magento\Framework\Controller\ResultFactory;
use Intenso\Review\Controller\Adminhtml\Comment as CommentController;

class Ham extends CommentController
{
    /**
     * Review model factory
     *
     * @var \Intenso\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Intenso\Review\Model\Comment
     */
    protected $commentFactory;

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
     * @param \Intenso\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Intenso\Review\Model\CommentFactory $commentFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Intenso\Review\Helper\Akismet $akismetHelper
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Intenso\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Intenso\Review\Model\CommentFactory $commentFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Intenso\Review\Helper\Akismet $akismetHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->customerSession = $customerSession;
        $this->commentFactory = $commentFactory;
        $this->storeManager = $storeManager;
        $this->akismetHelper = $akismetHelper;
        $this->customerFactory = $customerFactory;
        $this->logger = $logger;
        parent::__construct($context, $reviewFactory, $commentFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $commentId = $this->getRequest()->getParam('id', false);
        try {
            $comment = $this->commentFactory->create()->load($commentId);
            if ($comment->getCustomerId() > 0) {
                $customer = $this->customerFactory->create()->load($comment->getCustomerId());
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
            $this->akismetHelper->submitHam($akismetArray);
            $status = \Intenso\Review\Model\Review::STATUS_APPROVED;
            $comment->setStatusId($status)->save();
            $this->reviewFactory->create()->syncReviews($comment->getReviewId());
            $this->messageManager->addSuccess(__('The comment has been marked as "Not Spam" and published.'));
            $resultRedirect->setPath('intenso_review/*/');
            return $resultRedirect;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while updating this comment.'));
        }

        return $resultRedirect->setPath('intenso_review/*/edit/', ['id' => $commentId]);
    }
}
