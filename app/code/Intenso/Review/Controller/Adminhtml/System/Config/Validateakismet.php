<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\System\Config;

use Magento\Backend\App\Action;

/**
 * Validate Akismet API Key admin controller
 */
abstract class Validateakismet extends Action
{
    /**
     * Akismet helper
     *
     * @var \Intenso\Review\Helper\Akismet
     */
    protected $akismetHelper = null;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Intenso\Review\Helper\Akismet $akismetHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Intenso\Review\Helper\Akismet $akismetHelper
    ) {
        $this->akismetHelper = $akismetHelper;
        parent::__construct($context);
    }

    /**
     * Perform Akismet API key validation
     *
     * @return \Magento\Framework\DataObject
     */
    protected function _validate()
    {
        return $this->akismetHelper->checkAkismetApiKey(
            $this->getRequest()->getParam('akismet_api_key')
        );
    }
}
