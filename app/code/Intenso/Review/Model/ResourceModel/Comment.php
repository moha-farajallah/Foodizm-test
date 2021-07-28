<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

class Comment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     * Get table name from config
     * @return void
     */
    protected function _construct()
    {
        $this->_init('intenso_review_comment', 'entity_id');
    }

    /**
     * Proceed operations after object is saved
     * Sync review summary
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _afterSave(AbstractModel $object)
    {
        $this->_syncReviewSummary($object);
        return parent::_afterSave($object);
    }

    /**
     * Remove configuration data after delete website
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _afterDelete(AbstractModel $object)
    {
        $this->_syncReviewSummary($object);
        return parent::_afterDelete($object);
    }

    /**
     * Update review summary
     *
     * @param AbstractModel $object
     */
    protected function _syncReviewSummary(AbstractModel $object)
    {
        $reviewId = $object->getReviewId();
        $count = $this->_getCommentCountForReview($reviewId);
        $this->getConnection()->update(
            $this->getTable('intenso_review_summary'),
            ['comments' => $count],
            ['review_id = ?' => $reviewId]
        );
    }

    /**
     * Get comments count for review
     *
     * @param int $reviewId
     * @return int
     */
    protected function _getCommentCountForReview($reviewId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from($this->getMainTable(), ['count' => new \Zend_Db_Expr('COUNT(*)')])
            ->where('review_id = ?', $reviewId)
            ->where('status_id = ?', \Magento\Review\Model\Review::STATUS_APPROVED);
        $row = $connection->fetchRow($select);
        return $row['count'];
    }
}
