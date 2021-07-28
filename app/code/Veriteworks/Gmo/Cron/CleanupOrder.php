<?php
namespace Veriteworks\Gmo\Cron;

use \Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use \Magento\Framework\Registry;
use \Magento\Store\Model\StoreManagerInterface;

/**
 * Cleanup incompleted orders
 */
class CleanupOrder
{
    /**
     * cleanup order status
     */
    const ORDER_STATUS = 'payment_review';

    /**
     * @var CollectionFactory
     */
    private $orderCollection;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $date;
    /**
     * @var array
     */
    private $methodNames;

    /**
     * CleanupOrder constructor.
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Registry $registry
     * @param array $methodNames
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Registry $registry,
        array $methodNames = []
    ) {
        $this->orderCollection = $orderCollectionFactory;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->registry = $registry;
        $this->methodNames = $methodNames;
    }

    /**
     * execute job. Cleanup abandoned order
     */
    public function execute()
    {
        $fromDate = date('Y-m-d H:i:s', strtotime("-90 minutes", strtotime($this->date->gmtDate())));
        $toDate = date('Y-m-d H:i:s', strtotime("-30 minutes", strtotime($this->date->gmtDate())));
        $this->registry->register('isSecureArea', true);
        $this->registry->register('cancelling_review_order', true);
        $orders = $this->orderCollection->create()->addAttributeToSelect('*')
            ->addFieldToFilter('created_at', ['from'=>$fromDate, 'to'=>$toDate])
            ->addFieldToFilter('status', self::ORDER_STATUS);
        foreach ($orders as $order) {
            $method = $order->getPayment()->getMethod();
            if (in_array($method, $this->methodNames)) {
                $invoices = $order->getInvoiceCollection();
                foreach ($invoices as $invoice) {
                    $invoice->delete();
                }
                $ordered_items = $order->getAllItems();
                foreach ($ordered_items as $item) {
                    $item->setQtyInvoiced(0);
                    $item->save();
                }
                $order->setStatus('new');
                $order->setState('pending');
                $order->setCanSendCancelEmailFlag(false);
                $order->cancel();
                $order->delete();
            }
        }
    }
}
