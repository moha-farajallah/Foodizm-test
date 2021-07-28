<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model;

class Review extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Product entity review code
     */
    const ENTITY_PRODUCT_CODE = 'product';

    /**
     * Approved review/comment status code
     */
    const STATUS_APPROVED = 1;

    /**
     * Pending review/comment status code
     */
    const STATUS_PENDING = 2;

    /**
     * Not Approved review/comment status code
     */
    const STATUS_NOT_APPROVED = 3;

    /**
     * Spam review/comment status code
     */
    const STATUS_SPAM = 4;

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\ResourceModel\Review');
    }

    /**
     * Sync summary table with review table
     * @param int $id
     * @param null $isMap
     * @return Review
     */
    public function syncReviews($id = null, $isMap = null)
    {
        return $this->getResource()->syncReviews($id, $isMap);
    }
}
