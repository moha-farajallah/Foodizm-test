<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Sales\Model\OrderFactory as OrderFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\ProductFactory as ProductFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class Map extends AbstractModel
{
    /**
     * Mail After Purchase Threshold config path
     */
    const XML_PATH_MAP_THRESHOLD = 'intenso_review/map_options/mail_after_purchase_threshold';

    /**
     * MAP Bulk Interval config path
     */
    const XML_PATH_MAP_BULK_INTERVAL = 'intenso_review/map_options/map_bulk_interval';

    /**
     * MAP Template config path
     */
    const XML_PATH_MAP_TEMPLATE = 'intenso_review/map_options/map_template';

    /**
     * Customer sender email config path
     */
    const XML_PATH_CUSTOMER_EMAIL_SENDER = 'intenso_review/map_options/sender_email_identity';

    /**
     * Not-sent MAP status code
     */
    const STATUS_NOTSENT = 0;

    /**
     * Sent MAP status code
     */
    const STATUS_SENT = 1;

    /**
     * MAP review not posted status code
     */
    const REVIEW_NOT_POSTED = 0;

    /**
     * MAP review posted status code
     */
    const REVIEW_POSTED = 1;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Order factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Catalog product factory
     *
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * Core date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        OrderFactory $orderFactory,
        ProductFactory $productFactory,
        DateTime $date,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->productFactory = $productFactory;
        $this->date = $date;
    }

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\ResourceModel\Map');
    }

    /**
     * Send emails to subscribers for this queue
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function sendMailAfterPurchase()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $storeId = $this->getStoreId();
        $emailTemplate = $this->scopeConfig->getValue(
            self::XML_PATH_MAP_TEMPLATE,
            $storeScope,
            $storeId
        );

        // ignore current product if required days haven’t elapsed for secondary MAPs
        if ($this->getSortOrder() > 0) {
            $mapThreshold = $this->scopeConfig->getValue(
                self::XML_PATH_MAP_THRESHOLD,
                $storeScope,
                $storeId
            );
            $mapBulkInterval = $this->scopeConfig->getValue(
                self::XML_PATH_MAP_BULK_INTERVAL,
                $storeScope,
                $storeId
            );
            $mapThreshold = $mapThreshold + ($this->getSortOrder() * $mapBulkInterval);
            $orderDate = new \DateTime($this->getCreatedAt());
            $currentDate = new \DateTime('now');
            $daysElapsed = $orderDate->diff($currentDate)->format("%a");

            if ($mapThreshold > $daysElapsed) {
                return $this;
            }
        }

        $order = $this->orderFactory->create()->load($this->getOrderId());
        if ($order->getCustomerFirstname() == null) {
            $order->setCustomerFirstname($order->getBillingAddress()->getFirstname());
            $order->setCustomerLastname($order->getBillingAddress()->getLastname());
        }
        $product = $this->productFactory->create()->load($this->getProductId());
        $this->storeManager->setCurrentStore($order->getStoreId());

        // send MAP
        $transport = $this->transportBuilder->setTemplateIdentifier(
            $emailTemplate
        )->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ]
        )->setTemplateVars(
            [
                'map' => $this,
                'order' => $order,
                'product' => $product,
                'productUrl' => $product->getUrlInStore(['_store' => $order->getStoreId()]),
                'store' => $this->storeManager->getStore($storeId)
            ]
        )->setFrom(
            $this->scopeConfig->getValue(self::XML_PATH_CUSTOMER_EMAIL_SENDER, $storeScope, $storeId)
        )->addTo(
            $order->getCustomerEmail(),
            $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname()
        )->getTransport();

        try {
            $transport->sendMessage();
            $this->setEmailSent(1)
                ->setSentDate($this->date->gmtDate())
                ->save();
        } catch (\Exception $e) {
            $this->setProblemErrorCode($e->getCode())
                ->setProblemErrorText($e->getMessage())
                ->save();
        }
        return $this;
    }
}
