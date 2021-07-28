<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model;

use Magento\Store\Api\StoreRepositoryInterface;
use Intenso\Review\Model\ResourceModel\Map\CollectionFactory;

/**
 * Mail After Purchase observer
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Observer
{
    /**
     * MAP collection factory
     *
     * @var \Intenso\Review\Model\ResourceModel\Map\CollectionFactory
     */
    protected $mapCollectionFactory;

    /**
     * @var \Magento\Store\Api\StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * Construct
     *
     * @param \Intenso\Review\Model\ResourceModel\Map\CollectionFactory $mapCollectionFactory
     * @param \Magento\Store\Api\StoreRepositoryInterface $storeRepository
     */
    public function __construct(
        CollectionFactory $mapCollectionFactory,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->mapCollectionFactory = $mapCollectionFactory;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Scheduled send handler
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function scheduledSend()
    {
        $countOfQueue  = 20;

        foreach ($this->storeRepository->getList() as $store) {
            $storeId = $store->getId();

            if (!$storeId) {
                continue;
            }

            /** @var \Intenso\Review\Model\ResourceModel\Map\Collection $collection */
            $collection = $this->mapCollectionFactory->create();
            $collection->setPageSize($countOfQueue)->setCurPage(1)->addOnlyForSendingFilter($storeId)->load();

            $collection->walk('sendMailAfterPurchase');
        }
    }
}
