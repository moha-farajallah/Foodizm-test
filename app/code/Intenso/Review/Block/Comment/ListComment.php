<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Block\Comment;

use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObject\IdentityInterface;
use Intenso\Review\Model\ResourceModel\Comment\Collection as CommentCollection;

class ListComment extends Template implements IdentityInterface
{
    /**
     * Review collection
     *
     * @var CommentCollection
     */
    protected $commentsCollection;

    /**
     * Comment resource model
     *
     * @var \Intenso\Review\Model\ResourceModel\Comment\CollectionFactory
     */
    protected $commentsColFactory;

    /**
     * @var \Intenso\Review\Model\StoreOwnerCommentFactory
     */
    protected $storeOwnerCommentFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Intenso\Review\Model\ResourceModel\Comment\CollectionFactory $collectionFactory
     * @param \Intenso\Review\Model\StoreOwnerCommentFactory $storeOwnerCommentFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Intenso\Review\Model\ResourceModel\Comment\CollectionFactory $collectionFactory,
        \Intenso\Review\Model\StoreOwnerCommentFactory $storeOwnerCommentFactory,
        array $data = []
    ) {
        $this->commentsColFactory = $collectionFactory;
        $this->storeOwnerCommentFactory = $storeOwnerCommentFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return CommentCollection
     */
    public function getCommentsCollection()
    {
        if (null === $this->commentsCollection) {
            $this->commentsCollection = $this->commentsColFactory->create()
                ->addFieldToFilter('review_id', ['eq' => $this->getReviewId()])
                ->addFieldToFilter('status_id', ['eq' => \Magento\Review\Model\Review::STATUS_APPROVED]);
        }
        return $this->commentsCollection;
    }

    /**
     * @return mixed
     */
    public function getReviewId()
    {
        return $this->getRequest()->getParam('id', false);
    }

    /**
     * @return bool|\Intenso\Review\Model\StoreOwnerComment
     */
    public function getStoreOwnerComment()
    {
        $comment = $this->storeOwnerCommentFactory->create()->load($this->getReviewId(), 'review_id');

        if ($comment->getId()) {
            return $comment;
        } else {
            return false;
        }
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getCommentsCollection()) {
            foreach ($this->getCommentsCollection() as $item) {
                $identities = array_merge($identities, $item->getIdentities());
            }
        }
        return array_unique($identities);
    }
}
