<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel\Review;

class Collection extends \Magento\Review\Model\ResourceModel\Review\Collection
{
    /**
     * @var \Intenso\Review\Model\User
     */
    private $userData;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Review\Helper\Data $reviewData
     * @param \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Intenso\Review\Model\User $user
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Review\Helper\Data $reviewData,
        \Magento\Review\Model\Rating\Option\VoteFactory $voteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Intenso\Review\Model\User $userData,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $reviewData,
            $voteFactory,
            $storeManager
        );
        $this->userData = $userData;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    /**
     * Initialize select object
     *
     * @return $this
     */
    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()
            ->join(
                ['summary' => $this->getTable('intenso_review_summary')],
                'main_table.review_id = summary.review_id',
                '*'
            );
        return $this;
    }

    /**
     * Set order by number of votes
     *
     * @param string $dir
     * @return $this
     */
    public function setHelpfulOrder($dir = 'DESC')
    {
        $this->setOrder(new \Zend_Db_Expr('summary.helpful - summary.nothelpful'), $dir);
        $this->setOrder('main_table.created_at', $dir);
        return $this;
    }

    /**
     * Set 5-star filter
     *
     * @return $this
     */
    public function setFiveStarsFilter()
    {
        $this->addFieldToFilter('rating_summary', ['gt' => 89]);
        return $this;
    }

    /**
     * Set 4-star filter
     *
     * @return $this
     */
    public function setFourStarsFilter()
    {
        $this->addFieldToFilter('rating_summary', ['gt' => 79]);
        $this->addFieldToFilter('rating_summary', ['lt' => 90]);
        return $this;
    }

    /**
     * Set 3-star filter
     *
     * @return $this
     */
    public function setThreeStarsFilter()
    {
        $this->addFieldToFilter('rating_summary', ['gt' => 59]);
        $this->addFieldToFilter('rating_summary', ['lt' => 79]);
        return $this;
    }

    /**
     * Set 2-star filter
     *
     * @return $this
     */
    public function setTwoStarsFilter()
    {
        $this->addFieldToFilter('rating_summary', ['gt' => 39]);
        $this->addFieldToFilter('rating_summary', ['lt' => 59]);
        return $this;
    }

    /**
     * Set 1-star filter
     *
     * @return $this
     */
    public function setOneStarFilter()
    {
        $this->addFieldToFilter('rating_summary', ['lt' => 39]);
        return $this;
    }

    /**
     * Set verified purchase filter
     *
     * @return $this
     */
    public function setVerifiedPurchaseFilter()
    {
        $this->addFieldToFilter('summary.verified_purchase', ['eq' => 1]);
        return $this;
    }

    /**
     * Set date order
     *
     * @param string $dir
     * @return $this
     */
    public function setDateOrder($dir = 'DESC')
    {
        $this->setOrder('main_table.created_at', $dir);
        return $this;
    }

    /**
     * Join table intenso_review_vote to check if user has already voted
     *
     * @return $this
     */
    public function appendCanVote()
    {
        $customerId = -1;
        $guestId = -1;
        if ($this->customerSession->isLoggedIn()) {
            $customer = $customerData = $this->customerSession->getCustomer();
            if ($customer->getId()) {
                $customerId = $customer->getId();
            }
        }
        $guest = $this->userData->identify();
        if ($guest->getId()) {
            $guestId = $guest->getId();
        }

        $this->getSelect()
            ->joinLeft(
                ['vote' => $this->getTable('intenso_review_vote')],
                'main_table.review_id = vote.review_id AND (vote.guest_id = '.$guestId.' OR vote.customer_id = '.$customerId.')',
                ['can_vote' => new \Zend_Db_Expr("IF((vote.customer_id = {$customerId} OR vote.guest_id = {$guestId}),0,1)")]
            );
        return $this;
    }

    /**
     * Limit collection
     *
     * @return $this
     */
    public function limit($limit)
    {
        $this->getSelect()->limit($limit);
        return $this;
    }
}
