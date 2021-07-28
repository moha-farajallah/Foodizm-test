<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel\Image;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\Image', 'Intenso\Review\Model\ResourceModel\Image');
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

    /**
     * Set photo filter
     *
     * @param int $review_id
     * @return $this
     */
    public function addPhotoFilter($review_id)
    {
        $this->addFieldToFilter('review_id', ['eq' => $review_id]);
        return $this;
    }
}
