<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Comment;

use Magento\Framework\Controller\ResultFactory;
use Intenso\Review\Controller\Adminhtml\Comment as CommentController;

class Delete extends CommentController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $commentId = $this->getRequest()->getParam('id', false);
        try {
            $model = $this->commentFactory->create()->load($commentId);
            $reviewId = $model->getReviewId();
            $model->delete();
            $this->reviewFactory->create()->syncReviews($reviewId);
            $this->messageManager->addSuccess(__('The comment has been deleted.'));
            $resultRedirect->setPath('intenso_review/*/');
            return $resultRedirect;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while deleting this comment.'));
        }

        return $resultRedirect->setPath('intenso_review/*/edit/', ['id' => $commentId]);
    }
}
