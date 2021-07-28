<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

class Vote extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Approved vote status code
     */
    const CAN_VOTE = 1;

    /**
     * Already voted status code
     */
    const ALREADY_VOTED = 0;

    /**
     * Incorrect vote status code
     */
    const INCORRECT_VOTE = -1;

    /**
     * Eav config
     *
     * @var \Magento\Eav\Model\Config
     */
    private $reviewFactory;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * Store id
     *
     * @var \Magento\Store\Model\Store
     */
    private $storeId;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Psr\Log\LoggerInterface $logger,
        $connectionName = null
    ) {
        $this->storeManager = $storeManager;
        $this->reviewFactory = $reviewFactory;
        $this->logger = $logger;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     * Get table name from config
     * @return void
     */
    protected function _construct()
    {
        $this->_init('intenso_review_vote', 'entity_id');
    }

    /**
     * Update review_summary table after vote save
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this
     */
    protected function _afterSave(AbstractModel $object)
    {
        parent::_afterSave($object);
        $reviewId = $object->getReviewId();
        $summary = $this->getSummaryForReview($reviewId);
        if ($summary && $summary['total'] > 0) {
            $this->getConnection()->update(
                $this->getTable('intenso_review_summary'),
                ['helpful' => $summary['helpful'], 'nothelpful' => $summary['nothelpful']],
                ['review_id = ?' => $reviewId]
            );
        }
        return $this;
    }

    /**
     * Get review summary
     *
     * @param int $reviewId
     * @return array
     */
    private function getSummaryForReview($reviewId)
    {
        $connection = $this->getConnection();
        $sql = $connection->select()
            ->from($this->getMainTable(), [
                'total' => new \Zend_Db_Expr('COUNT(*)'),
                'helpful' => new \Zend_Db_Expr('SUM(helpful)'),
                'nothelpful' => new \Zend_Db_Expr('COUNT(*) - SUM(helpful)')])
            ->where('review_id = ?', $reviewId);
        return $connection->fetchRow($sql);
    }

    /**
     * Return store id.
     * If is not set return current app store
     *
     * @return integer
     */
    public function getStoreId()
    {
        if ($this->storeId === null) {
            $this->storeId = (int)$this->storeManager->getStore()->getId();
        }
        return $this->storeId;
    }

    /**
     * Check whether the user is allowed to vote or already voted
     * @param array $vote
     * @return int
     */
    public function canVote($vote = [])
    {
        $review = $this->reviewFactory->create()->load($vote['review_id']);
        if ($review->getId() && ((int)$review->getStoreId() === (int)$this->getStoreId() || (int)$review->getStoreId() === 0)) {
            $select = $this->getConnection()->select()->from(
                $this->getMainTable()
            )->where(
                'review_id = ?',
                $vote['review_id']
            );

            if ($vote['customer_id'] > 0 && $vote['guest_id'] > 0) {
                $select->where('customer_id = '.$vote['customer_id'].' OR guest_id = ?', $vote['guest_id']);
            } elseif ($vote['customer_id'] > 0) {
                $select->where('customer_id = ?', $vote['customer_id']);
            } else {
                $select->where('guest_id = ?', $vote['guest_id']);
            }
            $result = $this->getConnection()->query($select);

            if ($result->fetch()) {
                return self::ALREADY_VOTED;
            } else {
                return self::CAN_VOTE;
            }
        }
        return self::INCORRECT_VOTE;
    }
}
