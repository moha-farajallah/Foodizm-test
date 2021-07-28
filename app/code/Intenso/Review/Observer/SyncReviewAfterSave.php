<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
namespace Intenso\Review\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SyncReviewAfterSave implements ObserverInterface
{
    /**
     * @var \Intenso\Review\Model\Review
     */
    protected $reviews;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Intenso\Review\Model\Review $reviews
     * @param \Magento\Framework\Registry $registry
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Intenso\Review\Model\Review $reviews,
        \Magento\Framework\Registry $registry,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->reviews = $reviews;
        $this->registry = $registry;
        $this->logger = $logger;
    }

    /**
     * Sync summary table with review table
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $review = $this->registry->registry('current_review');
        $isMap = $this->registry->registry('is_verified_purchase');
        if ($review) {
            $this->reviews->syncReviews($review->getId(), $isMap);

            // save IP and user-agent to intenso_review_summary table
            if ($review->getUserIp() && $review->getUserAgent()) {
                $email = ($review->getEmail()) ? $review->getEmail() : null;
                $this->reviews->load($review->getId())->setGuestEmail($email)
                    ->setIp($review->getUserIp())
                    ->setHttpUserAgent($review->getUserAgent())
                    ->save();
            }
        } else {
            $this->reviews->syncReviews();
        }
        return $this;
    }
}
