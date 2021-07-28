<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Block\Category;

/**
 * Product reviews summary popover
 */
class Summary extends \Intenso\Review\Block\Product\ReviewRenderer
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Intenso\Review\Model\ReviewFactory $extendedReviewFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Intenso\Review\Model\ReviewFactory $extendedReviewFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        parent::__construct(
            $context,
            $reviewFactory,
            $extendedReviewFactory,
            $coreRegistry
        );
        $this->productRepository = $productRepository;
    }

    /**
     * Prepare layout
     *
     * @return \Intenso\Review\Block\Product\ReviewRenderer
     */
    protected function _prepareLayout()
    {
        try {
            $productId = $this->getRequest()->getParam('id');
            $product = $this->productRepository->getById($productId);
            $this->getLayout()->getBlock('product_review_histogram')->setData('product', $product);
            if (!$product->getRatingSummary()) {
                $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $noEntityException) {
            $product = null;
        }
        return parent::_prepareLayout();
    }
}
