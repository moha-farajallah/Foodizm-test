<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel;

class StoreOwnerComment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     * Get table name from config
     * @return void
     */
    protected function _construct()
    {
        $this->_init('intenso_review_storeowner_comment', 'entity_id');
    }
}
