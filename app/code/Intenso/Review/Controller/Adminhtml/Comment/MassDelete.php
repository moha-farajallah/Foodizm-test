<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Comment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;
use Intenso\Review\Controller\Adminhtml\Comment as CommentController;

class MassDelete extends CommentController
{
    /**
     * Review model factory
     *
     * @var \Intenso\Review\Model\ReviewFactory
     */
    protected $reviewFactory;

    /**
     * Comment model factory
     *
     * @var \Intenso\Review\Model\CommentFactory
     */
    protected $commentFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Intenso\Review\Model\ReviewFactory $reviewFactory
     * @param \Intenso\Review\Model\CommentFactory $commentFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Intenso\Review\Model\ReviewFactory $reviewFactory,
        \Intenso\Review\Model\CommentFactory $commentFactory
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->commentFactory = $commentFactory;
        parent::__construct($context, $reviewFactory, $commentFactory);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $commentIds = $this->getRequest()->getParam('comments');
        if (!is_array($commentIds)) {
            $this->messageManager->addError(__('Please select comment(s).'));
        } else {
            try {
                foreach ($commentIds as $commentId) {
                    $model = $this->commentFactory->create()->load($commentId);
                    $reviewId = $model->getReviewId();
                    $model->delete();
                    $this->reviewFactory->create()->syncReviews($reviewId);
                }
                $this->messageManager->addSuccess(
                    __('A total of %1 record(s) have been deleted.', count($commentIds))
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while deleting these comment(s).'));
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('intenso_review/*/' . $this->getRequest()->getParam('ret', 'index'));
        return $resultRedirect;
    }
}
