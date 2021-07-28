<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class Vote extends AbstractModel implements IdentityInterface
{
    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->reviewFactory = $reviewFactory;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\ResourceModel\Vote');
    }

    /**
     * Check if user can vote
     *
     * @param array $vote
     * @return int
     */
    public function canVote($vote = [])
    {
        return $this->getResource()->canVote($vote);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        $tags = [];
        $reviewFactory = $this->reviewFactory->create()->load($this->getReviewId());
        if ($reviewFactory->getEntityPkValue()) {
            $tags[] = \Intenso\Review\Model\Plugin\Review::CACHE_TAG . '_' . $reviewFactory->getEntityPkValue();
        }
        return $tags;
    }
}
