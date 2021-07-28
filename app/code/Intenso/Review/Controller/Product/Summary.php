<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
namespace Intenso\Review\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

class Summary extends Action
{
    /**
     * Display summary popover action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        return $resultLayout;
    }
}
