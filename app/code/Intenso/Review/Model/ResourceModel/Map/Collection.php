<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel\Map;

use Psr\Log\LoggerInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Data\Collection\EntityFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Mail After Purchase Threshold config path
     */
    const XML_PATH_MAP_THRESHOLD = 'intenso_review/map_options/mail_after_purchase_threshold';

    /**
     * Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource
     */
    public function __construct(
        EntityFactory $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        DateTime $date,
        ScopeConfigInterface $scopeConfig,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->date = $date;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Constructor
     * Configures collection
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\Map', 'Intenso\Review\Model\ResourceModel\Map');
    }

    /**
     * Add filter by only ready for sending item
     *
     * @param int $storeId
     * @return $this
     */
    public function addOnlyForSendingFilter($storeId)
    {
        $mapThreshold = $this->scopeConfig->getValue(
            self::XML_PATH_MAP_THRESHOLD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $this->addFieldToFilter('email_sent', 0);
        $this->addFieldToFilter('store_id', $storeId);
        $this->getSelect()->where(
            new \Zend_Db_Expr('TIME_TO_SEC(TIMEDIFF(CURRENT_TIMESTAMP, `created_at`)) >= ' . $mapThreshold * 86400)
        );

        return $this;
    }

    /**
     * Join fields to entity
     *
     * @return $this
     */
    public function appendOrderData()
    {
        $this->getSelect()->join(
            ['orders' => $this->getTable('sales_order')],
            'orders.entity_id = main_table.order_id',
            ['orders.increment_id','orders.customer_email']
        );
        return $this;
    }

    /**
     * Join fields to entity
     *
     * @return $this
     */
    public function appendProductData()
    {
        $this->getSelect()->join(
            ['products' => $this->getTable('catalog_product_entity')],
            'products.entity_id = main_table.product_id',
            'products.sku'
        );
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
