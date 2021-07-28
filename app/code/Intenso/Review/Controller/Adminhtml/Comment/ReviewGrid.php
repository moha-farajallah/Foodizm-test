<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\Comment;

use Magento\Framework\Controller\ResultFactory;
use Intenso\Review\Controller\Adminhtml\Comment as CommentController;

class ReviewGrid extends CommentController
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

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
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Intenso\Review\Model\ReviewFactory $reviewFactory,
        \Intenso\Review\Model\CommentFactory $commentFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory
    ) {
        $this->layoutFactory = $layoutFactory;
        $this->commentFactory = $commentFactory;
        parent::__construct($context, $reviewFactory, $commentFactory);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $layout = $this->layoutFactory->create();
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        $resultRaw->setContents($layout->createBlock('Intenso\Review\Block\Adminhtml\Comment\Grid')->toHtml());
        return $resultRaw;
    }
}
