<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Comment;

use Magento\Framework\Controller\ResultFactory;
use Intenso\Review\Controller\Adminhtml\Comment as CommentController;

class Index extends CommentController
{
    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->getRequest()->getParam('ajax')) {
            /** @var \Magento\Backend\Model\View\Result\Forward $resultForward */
            $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
            $resultForward->forward('reviewGrid');
            return $resultForward;
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Intenso_Review::catalog_reviews_comments_all');
        $resultPage->getConfig()->getTitle()->prepend(__('Comments on Reviews'));
        $resultPage->addContent($resultPage->getLayout()->createBlock('Intenso\Review\Block\Adminhtml\Comment\Main'));
        return $resultPage;
    }
}
