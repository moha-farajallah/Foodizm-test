<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Block\Map;

class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * Get store name
     *
     * @return null|string
     */
    public function getStoreName()
    {
        return $this->_storeManager->getStore()->getFrontendName();
    }
}
