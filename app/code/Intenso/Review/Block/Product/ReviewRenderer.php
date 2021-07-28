<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Block\Product;

use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

class ReviewRenderer extends \Magento\Review\Block\Product\ReviewRenderer
{
    /**
     * Reviews max words preview setting
     */
    const XML_PATH_DISPLAY_SUMMARY_POPOVER = 'intenso_review/configuration/summary_popover';

    /**
     * Display review count on catalog page
     */
    const XML_PATH_REVIEW_COUNT = 'intenso_review/configuration/review_count';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * Review model factory
     *
     * @var \Intenso\Review\Model\ReviewFactory
     */
    protected $extendedReviewFactory;

    /**
     * Array of available template name
     *
     * @var array
     */
    protected $_availableTemplates = [
        self::FULL_VIEW => 'Intenso_Review::helper/summary.phtml',
        self::SHORT_VIEW => 'Intenso_Review::helper/summary_short.phtml',
    ];

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Intenso\Review\Model\ReviewFactory $extendedReviewFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Intenso\Review\Model\ReviewFactory $extendedReviewFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $reviewFactory, $data);
        $this->extendedReviewFactory = $extendedReviewFactory;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * Get current product from registry
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->getData('product')) {
            $product = $this->coreRegistry->registry('product');
            $this->setData('product', $product);
            if (!$product->getRatingSummary()) {
                $this->_reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
            }
        }
        return $this->getData('product');
    }

    /**
     * Retrieve reviews stats
     *
     * @param int $productId
     * @return array
     */
    public function getProductStat($productId)
    {
        $sum = $count = 0;
        $stars = $meter = $percentage = $ratioNormalized = [];
        $stat = $this->extendedReviewFactory->create()->getResource()->getStat($productId);
        foreach ($stat as $row) {
            $sum+=$row['count'] * $row['value'];
            $count+=$row['count'];
            $stars[$row['value']] = $row['count'];
        }
        $maxValue = (count($stars) > 0) ?max($stars):5;
        
        $rating = number_format(round($sum/$count, 2), 2);
        for ($i=5; $i >= 1; $i--) {
            if (!isset($stars[$i])) {
                $stars[$i] = 0;
            }
            $percentage[$i] = ($stars[$i] > 0) ? round($stars[$i] / $count * 100) : 0;
        }
        // calculate meter bar percentage
        for ($i=5; $i >= 1; $i--) {
            $meter[$i] = ($stars[$i] > 0) ? $stars[$i] / $maxValue * 100 : 0;
        }
        return [$rating,$meter,$percentage];
    }

    /**
     * Get count of reviews
     *
     * @return int
     */
    public function getReviewsCount()
    {
        $product = $this->getProduct();

        if ($product->getRatingSummary() instanceof DataObject) {
            return $this->getProduct()->getRatingSummary()->getReviewsCount();
        }

        return $product->getReviewsCount();
    }

    /**
     * Check whether to show review count
     *
     * @param null|string|bool|int $store
     * @return int
     */
    public function canDisplayReviewCount($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_REVIEW_COUNT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get review product list url
     *
     * @param bool $useDirectLink allows to use direct link for product reviews page
     * @return string
     */
    public function getReviewsUrl($useDirectLink = false)
    {
        $product = $this->getProduct();
        if ($useDirectLink) {
            $path = ($this->getIsAjax()) ? 'review/product/listAjax' : 'review/product/list';
            return $this->getUrl(
                $path,
                ['id' => $product->getId(), 'category' => $product->getCategoryId()]
            );
        }
        return $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
    }

    /**
     * Get config value for summary popover box
     *
     * @param null|string|bool|int $store
     * @return bool
     */
    public function displaySummaryPopover($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_SUMMARY_POPOVER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Summary popover url
     *
     * @param int $productId
     * @return string
     */
    public function getSummaryUrl($productId)
    {
        return $this->getUrl(
            'intenso_review/product/summary',
            ['id' => $productId]
        );
    }
}
