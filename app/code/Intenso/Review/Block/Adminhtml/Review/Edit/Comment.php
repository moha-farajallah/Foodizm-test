<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

/**
 * Adminhtml Review Photo
 */
namespace Intenso\Review\Block\Adminhtml\Review\Edit;

class Comment extends \Magento\Backend\Block\Template
{
    /**
     * Store owner comment template
     *
     * @var string
     */
    protected $_template = 'Intenso_Review::review/comment.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Store owner comment collection factory
     *
     * @var \Intenso\Review\Model\ResourceModel\StoreOwnerComment\CollectionFactory
     */
    protected $_commentCollectionFactory;

    /**
     * Store owner comment
     *
     * @var \Intenso\Review\Model\StoreOwnerComment
     */
    protected $_storeOwnerComment;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Intenso\Review\Model\ResourceModel\StoreOwnerComment\CollectionFactory $commentCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Intenso\Review\Model\ResourceModel\StoreOwnerComment\CollectionFactory $commentCollectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_commentCollectionFactory = $commentCollectionFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize review data
     *
     * @return void
     */
    protected function _construct()
    {
        if ($this->_coreRegistry->registry('review_data')) {
            $this->setReviewId($this->_coreRegistry->registry('review_data')->getId());
        }
    }

    /**
     * Get store owner comment for current review
     *
     * @return \Intenso\Review\Model\StoreOwnerComment
     */
    public function getReviewComment()
    {
        if ($this->_storeOwnerComment === null) {
            $this->_storeOwnerComment = $this->_commentCollectionFactory->create()
                ->setReviewFilter($this->getReviewId())
                ->getFirstItem();
        }
        return $this->_storeOwnerComment;
    }

    /**
     * Get store owner comment post action
     *
     * @return string
     */
    public function getAction()
    {
        return $this->getUrl(
            'intenso_review/comment/saveStoreOwnerComment',
            [
                '_secure' => $this->getRequest()->isSecure()
            ]
        );
    }
}
