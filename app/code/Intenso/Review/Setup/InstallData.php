<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Setup;

use Intenso\Review\Model\Review;
use Magento\Framework\App\State;
use Intenso\Review\Model\ReviewFactory;
use Magento\Review\Model\Review\StatusFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var Review setup factory
     */
    private $reviewFactory;

    /**
     * @var Review Status setup factory
     */
    private $statusFactory;

    /**
     * @param State $appState
     * @param ReviewFactory $review
     * @param StatusFactory $status
     */
    public function __construct(
        State $appState,
        ReviewFactory $review,
        StatusFactory $status
    ) {
        $this->reviewFactory = $review;
        $this->statusFactory = $status;

        $appState->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $statusCollection = $this->statusFactory->create()
            ->getCollection()
            ->addFieldToFilter('status_id', Review::STATUS_SPAM);

        if (!$statusCollection->getSize()) {
            //Add Spam status to review/review_status
            $bind = ['status_id' => Review::STATUS_SPAM, 'status_code' => 'Spam'];
            $installer->getConnection()->insertForce($installer->getTable('review_status'), $bind);
        }

        $this->reviewFactory->create()->syncReviews();
    }
}
