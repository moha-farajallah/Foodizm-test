<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
namespace Intenso\Review\Observer;

use Magento\Framework\Registry;
use Magento\Review\Model\Review;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ReviewAfterSave implements ObserverInterface
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @param Registry $registry
     */
    public function __construct(
        Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * Save review object in registry
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        $review = $observer->getEvent()->getObject();
        if ($review instanceof Review) {
            $this->registry->unregister('current_review');
            $this->registry->register('current_review', $review);
        }
        return $this;
    }
}
