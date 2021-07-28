<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

/**
 * Adminhtml Review Photo
 */
namespace Intenso\Review\Block\Adminhtml\Review\Edit;

class Photo extends \Magento\Backend\Block\Template
{
    /**
     * Customer photos template name
     *
     * @var string
     */
    protected $_template = 'Intenso_Review::review/photo.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Image collection factory
     *
     * @var \Intenso\Review\Model\ResourceModel\Image\CollectionFactory
     */
    protected $_imageCollectionFactory;

    /**
     * Photos collection
     *
     * @var \Intenso\Review\Model\ResourceModel\Image\Collection
     */
    protected $_photosCollection;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Intenso\Review\Model\ResourceModel\Image\CollectionFactory $imageCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Intenso\Review\Model\ResourceModel\Image\CollectionFactory $imageCollectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_imageCollectionFactory = $imageCollectionFactory;
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
     * Get collection of photos for current review
     *
     * @return \Intenso\Review\Model\ResourceModel\Image\Collection|bool
     */
    public function getReviewPhotos()
    {
        if ($this->_photosCollection === null) {
            $this->_photosCollection = $this->_imageCollectionFactory->create()->setReviewFilter(
                $this->getReviewId()
            )->load();
        }
        return $this->_photosCollection;
    }

    /**
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'reviews';
    }
}
