<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * MAP log admin controller
 */
abstract class Maplog extends Action
{
    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['edit'];

    /**
     * Review model factory
     *
     * @var \Intenso\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * MAP model factory
     *
     * @var \Intenso\Review\Model\MapFactory
     */
    protected $mapFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Intenso\Review\Model\ReviewFactory $reviewFactory
     * @param \Intenso\Review\Model\MapFactory $mapFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Intenso\Review\Model\ReviewFactory $reviewFactory,
        \Intenso\Review\Model\MapFactory $mapFactory
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->mapFactory = $mapFactory;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Intenso_Review::maplog_manage');
    }
}
