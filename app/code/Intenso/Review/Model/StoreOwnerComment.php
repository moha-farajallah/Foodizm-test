<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model;

class StoreOwnerComment extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->_date = $date;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\ResourceModel\StoreOwnerComment');
    }

    /**
     * Clear cache related with review id
     *
     * @return $this
     */
    protected function cleanCache()
    {
        $reviewFactory = $this->reviewFactory->create()->load($this->getReviewId());
        if ($reviewFactory->getEntityPkValue()) {
            $this->_cacheManager->clean(\Intenso\Review\Model\Plugin\Review::CACHE_TAG . '_' . $reviewFactory->getEntityPkValue());
        }
        return $this;
    }

    /**
     * Set date of last update for comment
     *
     * @return $this
     */
    public function beforeSave()
    {
        parent::beforeSave();
        $this->setUpdatedAt($this->_date->gmtDate());
        return $this;
    }

    /**
     * Processing object after save data
     *
     * @return \Magento\Framework\Model\AbstractModel
     */
    public function afterSave()
    {
        $this->cleanCache();
        $review = $this->reviewFactory->create()->load($this->getReviewId());
        $this->_eventManager->dispatch('clean_cache_by_tags', ['object' => $review]);
        return parent::afterSave();
    }
}
