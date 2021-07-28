<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel\User;

/**
 * User data collection
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Constructor - Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\User', 'Intenso\Review\Model\ResourceModel\User');
    }

    /**
     * Get user by IP and user agent
     *
     * @param string $ip
     * @param string $userAgent
     * @return $this
     */
    public function getUserByIpAndUserAgent($ip, $userAgent)
    {
        $this->addFieldToFilter('ip', ['eq' => $ip])
            ->addFieldToFilter('http_user_agent', ['eq' => $userAgent]);
        return $this;
    }
}
