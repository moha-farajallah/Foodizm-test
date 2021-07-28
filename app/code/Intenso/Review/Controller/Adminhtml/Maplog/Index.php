<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Maplog;

use Magento\Framework\Controller\ResultFactory;
use Intenso\Review\Controller\Adminhtml\Maplog as MaplogController;

class Index extends MaplogController
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('ajax')) {
            /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('maplogGrid');
            return $resultForward;
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Intenso_Review::map_log');
        $resultPage->getConfig()->getTitle()->prepend(__('Mail After Purchase Log'));
        $resultPage->addContent($resultPage->getLayout()->createBlock('Intenso\Review\Block\Adminhtml\Maplog\Main'));
        return $resultPage;
    }
}
