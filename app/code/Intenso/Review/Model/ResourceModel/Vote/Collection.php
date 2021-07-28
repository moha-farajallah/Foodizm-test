<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel\Vote;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor
     * Configures collection
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\Vote', 'Intenso\Review\Model\ResourceModel\Vote');
    }

    /**
     * Filter collection by specified store ids
     *
     * @param int $storeId
     * @return $this
     */
    public function addStoreFilter($storeId = null)
    {
        if ($storeId) {
            $this->getSelect()
                ->join(['rstore' => $this->getTable('review/review_store')], 'main_table.review_id = rstore.review_id')
                ->where('rstore.store_id = ?', $storeId);
        }
        return $this;
    }
}
