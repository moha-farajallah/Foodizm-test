<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
namespace Intenso\Review\Observer;

use Psr\Log\LoggerInterface;
use Intenso\Review\Model\MapFactory;
use Magento\Framework\Event\Observer;
use Magento\Review\Model\ReviewFactory;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MailAfterPurchase implements ObserverInterface
{
    /**
     * Enable MAP config path
     */
    const XML_PATH_MAP_ENABLED = 'intenso_review/map_options/enable_map';

    /**
     * MAP bulk emails limit config path
     */
    const XML_PATH_MAP_BULK_EMAILS = 'intenso_review/map_options/map_bulk_emails';

    /**
     * MAP sort order config path
     */
    const XML_PATH_MAP_SORT_ORDER = 'intenso_review/map_options/map_sort_order';

    /**
     * @var \Intenso\Review\Model\MapFactory
     */
    protected $mapFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Core date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Intenso\Review\Model\MapFactory $mapFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        MapFactory $mapFactory,
        ProductRepositoryInterface $productRepository,
        ReviewFactory $reviewFactory,
        ScopeConfigInterface $scopeConfig,
        DateTime $date,
        LoggerInterface $logger
    ) {
        $this->mapFactory = $mapFactory;
        $this->productRepository = $productRepository;
        $this->reviewFactory = $reviewFactory;
        $this->scopeConfig = $scopeConfig;
        $this->date = $date;
        $this->logger = $logger;
    }

    /**
     * Add Mail After Purchase to queue
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        try {
            $order = $observer->getEvent()->getOrder();
            if ($order->getStatus() == \Magento\Sales\Model\Order::STATE_COMPLETE) {
                $storeId = $order->getStoreId();

                $mapEnabled = $this->scopeConfig->getValue(
                    self::XML_PATH_MAP_ENABLED,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                if (!$mapEnabled) {
                    return $this;
                }

                $products = $this->getProductsFromOrder($order);
                $mapBulkEmails = $this->scopeConfig->getValue(
                    self::XML_PATH_MAP_BULK_EMAILS,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                foreach ($products as $key => $value) {
                    if ($mapBulkEmails <= $key) {
                        break;
                    }
                    $token = bin2hex(openssl_random_pseudo_bytes(16));
                    $map = $this->mapFactory->create();
                    $map->setOrderId($order->getId())
                        ->setProductId($value['id'])
                        ->setCreatedAt($this->date->gmtDate())
                        ->setCustomerToken($token)
                        ->setSortOrder($key)
                        ->setStoreId($storeId)
                        ->save();
                }
            }
            return $this;
        } catch (\Exception $e) {
            $this->logger->notice($e->getMessage());
            return $this;
        }
    }

    /**
     * Get all products from order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function getProductsFromOrder($order)
    {
        $output = [];
        $products = $order->getAllVisibleItems();
        $storeId = $order->getStoreId();
        $mapSortOrder = $this->scopeConfig->getValue(
            self::XML_PATH_MAP_SORT_ORDER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        foreach ($products as $item) {
            $product = $this->productRepository->get($item->getSku());
            $parentId = $item->getProduct()->getId();
            if (!empty($parentId)) {
                $product = $this->productRepository->getById($parentId);
            }
            $productData = [];
            $productData['id'] = $product->getId();
            $productData['price'] = $item->getPrice();
            if ($mapSortOrder == 'reviews') {
                $this->reviewFactory->create()->getEntitySummary($product, $storeId);
                $productData['reviews'] = $product->getRatingSummary()->getReviewsCount();
            }
            $output[] = $productData;
        }

        // sort array by price or number of reviews depending on Config
        $helper = [];
        foreach ($output as $key => $value) {
            $helper[$key] = $value[$mapSortOrder];
        }
        if ($mapSortOrder == 'reviews') {
            array_multisort($helper, SORT_ASC, $output);
        } else {
            array_multisort($helper, SORT_DESC, $output);
        }
        return $output;
    }
}
