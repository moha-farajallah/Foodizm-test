<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Comment;

use Magento\Framework\Controller\ResultFactory;
use Intenso\Review\Controller\Adminhtml\Comment as CommentController;

class Edit extends CommentController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Intenso_Review::catalog_reviews_comments_all');
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Comment'));
        $resultPage->addContent($resultPage->getLayout()->createBlock('Intenso\Review\Block\Adminhtml\Comment\Edit'));
        return $resultPage;
    }
}
