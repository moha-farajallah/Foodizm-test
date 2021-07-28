<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel\StoreOwnerComment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\StoreOwnerComment', 'Intenso\Review\Model\ResourceModel\StoreOwnerComment');
    }

    /**
     * Set review filter
     *
     * @param int $reviewId
     * @return $this
     */
    public function setReviewFilter($reviewId)
    {
        $this->addFieldToFilter('review_id', $reviewId);
        return $this;
    }
}
