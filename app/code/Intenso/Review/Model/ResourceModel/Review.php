<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;

class Review extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->customerSession = $customerSession;
        $this->coreRegistry = $registry;
        $this->storeManager = $storeManager;
        parent::__construct($context, $connectionName);
    }

    /**
     * Initialize resource model
     * Get table name from config
     * @return void
     */
    protected function _construct()
    {
        $this->_init('intenso_review_summary', 'review_id');
    }

    /**
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->orders) {
            $this->orders = $this->orderCollectionFactory->create()->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'customer_id',
                $customerId
            )->addFieldToFilter(
                'status',
                \Magento\Sales\Model\Order::STATE_COMPLETE
            )->setOrder(
                'created_at',
                'desc'
            );
        }
        return $this->orders;
    }

    /**
     * Check if logged in customer has purchased the product
     *
     * @param int $productId
     * @return int
     */
    public function verifyPurchase($productId)
    {
        if (!$this->customerSession->getCustomerId()) {
            return 0;
        }
        $orders = $this->getOrders();
        foreach ($orders as $order) {
            $products = $order->getAllVisibleItems();
            foreach ($products as $item) {
                if ($item->getProduct()->getId() == $productId) {
                    return 1;
                }
            }
        }
        return 0;
    }

    /**
     * Sync summary table with review table
     *
     * @param int $reviewId
     * @param bool $isMap
     * @return Review
     */
    public function syncReviews($reviewId = null, $isMap = null)
    {
        $connection = $this->getConnection();
        $verifiedPurchase = 0;
        $select = $connection->select()->from(['rtable' => $this->getTable('review')], ['review_id']);

        if ($reviewId !== null) {
            $select->where('rtable.review_id in (?)', $reviewId);
            if ($product = $this->coreRegistry->registry('product')) {
                $verifiedPurchase = $this->verifyPurchase($product->getId());
            } elseif ($isMap) {
                $verifiedPurchase = $isMap;
            }
        }

        $select->join(
            ['rov' => $this->getTable('rating_option_vote')],
            'rtable.review_id = rov.review_id and rtable.entity_pk_value = rov.entity_pk_value',
            [
                'rating_summary' => new \Zend_Db_Expr('SUM(rov.percent)/COUNT(rov.percent)'),
            ]
        )
        ->group('rtable.review_id')
        ->columns(
            [
                'helpful' => new \Zend_Db_Expr('0'),
                'nothelpful' => new \Zend_Db_Expr('0'),
                'comments' => new \Zend_Db_Expr('0'),
                'guest_email' => new \Zend_Db_Expr('NULL'),
                'verified_purchase' => new \Zend_Db_Expr($verifiedPurchase),
                'ip' => new \Zend_Db_Expr('NULL'),
                'http_user_agent' => new \Zend_Db_Expr('NULL')
            ]
        );

        $connection->query(
            $this->_insertFromSelect(
                $select,
                $this->getTable('intenso_review_summary')
            )
        );
        return $this;
    }

    /**
     * Get insert from Select object query
     *
     * @param Select $select
     * @param string $table     insert into table
     * @return string
     */
    protected function _insertFromSelect(Select $select, $table)
    {
        $query = 'INSERT INTO `' . $table . '` (' . $select->__toString() . ') ON DUPLICATE KEY UPDATE `rating_summary` = VALUES(`rating_summary`)';
        return $query;
    }

    /**
     * Retrieve reviews stats
     *
     * @param int $productId
     * @return array
     */
    public function getStat($productId)
    {
        $result = [];
        if ($productId > 0) {
            $connection = $this->getConnection();
            $storeId = $this->storeManager->getStore()->getId();
            $select = $connection->select()
                ->from(['main_table' => $this->getTable('review')], 'main_table.review_id')
                ->where('main_table.entity_pk_value = ?', $productId)
                ->where('main_table.status_id = ?', \Magento\Review\Model\Review::STATUS_APPROVED)
                ->where('rstore.store_id = ?', $storeId)
                ->join(['rdetails' => $this->getTable('review_detail')], 'rdetails.review_id = main_table.review_id', [])
                ->join(['rstore' => $this->getTable('review_store')], 'rstore.review_id = main_table.review_id', [])
                ->join(['votes' => $this->getTable('rating_option_vote')], 'main_table.review_id = votes.review_id', ['value' => new \Zend_Db_Expr('ROUND(AVG(votes.percent)/20)')])
                ->group('main_table.review_id');
            $SqlString = $select->assemble();

            $sql = $connection->select()
                ->from(new \Zend_Db_Expr("($SqlString)"), ['value', 'count' => new \Zend_Db_Expr('COUNT(*)')])
                ->group('value')
                ->order('value DESC');
            $result = $connection->fetchAll($sql);
        }
        return $result;
    }
}
