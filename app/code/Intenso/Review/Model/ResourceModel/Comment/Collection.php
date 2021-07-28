<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel\Comment;

use Magento\Framework\DB\Select;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\Comment', 'Intenso\Review\Model\ResourceModel\Comment');
    }

    /**
     * Join fields to entity
     *
     * @return $this
     */
    public function appendReviewTitle()
    {
        $reviewDetailTable = $this->getTable('review_detail');
        $this->getSelect()->join(
            ['rdt' => $reviewDetailTable],
            'rdt.review_id = main_table.review_id',
            'rdt.title'
        );
        return $this;
    }

    /**
     * Join fields to entity
     *
     * @return $this
     */
    public function appendCustomerData()
    {
        $reviewDetailTable = $this->getTable('review_detail');
        $this->getSelect()->join(
            ['rdt' => $reviewDetailTable],
            'rdt.review_id = main_table.review_id',
            'rdt.title'
        );
        return $this;
    }

    /**
     * Exclude spam
     *
     * @return $this
     */
    public function excludeSpam()
    {
        $this->addFieldToFilter('status_id', ['lt' => 4]);
        return $this;
    }

    /**
     * Get result sorted ids
     *
     * @return array
     */
    public function getResultingIds()
    {
        $idsSelect = clone $this->getSelect();
        $idsSelect->reset(Select::LIMIT_COUNT);
        $idsSelect->reset(Select::LIMIT_OFFSET);
        $idsSelect->reset(Select::COLUMNS);
        $idsSelect->reset(Select::ORDER);
        $idsSelect->columns('entity_id');
        return $this->getConnection()->fetchCol($idsSelect);
    }
}
