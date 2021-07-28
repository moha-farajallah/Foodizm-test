<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Map extends AbstractDb
{
    /**
     * Initialize resource model
     * Get table name from config
     * @return void
     */
    protected function _construct()
    {
        $this->_init('intenso_review_map', 'entity_id');
    }
}
