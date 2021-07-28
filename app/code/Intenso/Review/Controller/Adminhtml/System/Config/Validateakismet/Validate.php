<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\System\Config\Validateakismet;

use Intenso\Review\Controller\Adminhtml\System\Config\Validateakismet as ValidateController;

class Validate extends ValidateController
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Intenso\Review\Helper\Akismet $akismetHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Intenso\Review\Helper\Akismet $akismetHelper,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context, $akismetHelper);
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Check whether Akismet API Key is valid
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->_validate();

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'valid' => (int)$result->getIsValid(),
            'message' => $result->getRequestMessage(),
        ]);
    }
}
