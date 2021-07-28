<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Block\Product\View;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Detailed Product Reviews
 */
class ListView extends \Magento\Review\Block\Product\View
{
    /**
     * Verified purchase badge config path
     */
    const XML_VERIFIED_PURCHASE_BADGE = 'intenso_review/verified_purchase/enable_badge';

    /**
     * Store owner comment expanded mode setting
     */
    const XML_PATH_DISPLAY_IN_EXPANDED_MODE = 'intenso_review/store_owner_comments/expanded_mode';

    /**
     * Intenso helper
     *
     * @var \Intenso\Review\Helper\Data
     */
    protected $intensoHelper;

    /**
     * Review collection
     *
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    protected $reviewsCollectionSummary;

    /**
     * Image collection
     *
     * @var \Intenso\Review\Model\ResourceModel\Image\CollectionFactory
     */
    protected $imageCollectionFactory;

    /**
     * @var \Intenso\Review\Model\StoreOwnerCommentFactory
     */
    protected $storeOwnerCommentFactory;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Customer\Model\Session $customerSession
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory
     * @param \Intenso\Review\Model\ResourceModel\Image\CollectionFactory $imageCollectionFactory
     * @param \Intenso\Review\Helper\Data $intensoHelper
     * @param \Intenso\Review\Model\StoreOwnerCommentFactory $storeOwnerCommentFactory
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        \Intenso\Review\Model\ResourceModel\Image\CollectionFactory $imageCollectionFactory,
        \Intenso\Review\Helper\Data $intensoHelper,
        \Intenso\Review\Model\StoreOwnerCommentFactory $storeOwnerCommentFactory,
        array $data = []
    ) {
        $this->imageCollectionFactory = $imageCollectionFactory;
        $this->intensoHelper = $intensoHelper;
        $this->storeOwnerCommentFactory = $storeOwnerCommentFactory;
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $collectionFactory,
            $data
        );
    }

    /**
     * Get product id
     *
     * @return int|null
     */
    public function getProductId()
    {
        $product = $this->_coreRegistry->registry('product');
        return $product ? $product->getId() : null;
    }

    /**
     * Prepare product review list toolbar
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $toolbar = $this->getLayout()->getBlock('product_review_list.toolbar');
        if ($toolbar) {
            $toolbar->setCollection($this->getReviewsCollection());
            $this->setChild('toolbar', $toolbar);
        }

        return $this;
    }

    /**
     * Add rate votes
     *
     * @return \Magento\Review\Block\Product\View
     */
    protected function _beforeToHtml()
    {
        $this->getReviewsCollection()->load()->addRateVotes();
        return parent::_beforeToHtml();
    }

    /**
     * Get collection of reviews
     *
     * @return \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    public function getReviewsCollectionSummary()
    {
        if (null === $this->reviewsCollectionSummary) {
            $limit = $this->intensoHelper->getNumReviewsForProductPage();
            $this->reviewsCollectionSummary = $this->_reviewsColFactory->create()->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $this->getProduct()->getId()
            )->appendCanVote()
            ->setHelpfulOrder('DESC')
            ->limit($limit);
        }

        return $this->reviewsCollectionSummary;
    }

    /**
     * Get collection of reviews
     *
     * @return \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    public function getReviewsCollection()
    {
        if (null === $this->_reviewsCollection) {
            $this->_reviewsCollection = $this->_reviewsColFactory->create()->addStoreFilter(
                $this->_storeManager->getStore()->getId()
            )->addStatusFilter(
                \Magento\Review\Model\Review::STATUS_APPROVED
            )->addEntityFilter(
                'product',
                $this->getProduct()->getId()
            )->appendCanVote();
        }

        return $this->_reviewsCollection;
    }

    /**
     * Get collection of photos for a review
     *
     * @param int $review_id
     * @return \Intenso\Review\Model\ResourceModel\Image\Collection
     */
    public function getReviewsPhotos($review_id)
    {
        return $this->imageCollectionFactory->create()->addPhotoFilter($review_id);
    }

    /**
     * Return review url
     *
     * @param int $id
     * @return string
     */
    public function getReviewUrl($id)
    {
        return $this->getUrl('*/*/view', ['id' => $id]);
    }

    /**
     * @return string
     */
    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'reviews';
    }

    /**
     * Return number of reviews to be displayed
     *
     * @return int
     */
    public function getNumReviewsForProductPage()
    {
        return $this->intensoHelper->getNumReviewsForProductPage();
    }

    /**
     * Return number of words to display before the "Read more" link
     *
     * @return int
     */
    public function getNumberOfWords()
    {
        return $this->intensoHelper->getNumberOfWords();
    }

    /**
     * Check whether customer is logged in
     *
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return $this->customerSession->isLoggedIn();
    }

    /**
     * @return bool
     */
    public function canShowVerifiedPurchaseBadge()
    {
        return $this->_scopeConfig->getValue(
            self::XML_VERIFIED_PURCHASE_BADGE,
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId()
        );
    }

    /**
     * Check whether customers can upload photos
     *
     * @return bool
     */
    public function canUploadPhotos()
    {
        return $this->intensoHelper->canUploadPhotos();
    }

    /**
     * @param int $review_id
     * @return bool|\Intenso\Review\Model\StoreOwnerComment
     */
    public function getStoreOwnerComment($review_id)
    {
        $comment = $this->storeOwnerCommentFactory->create()->load($review_id, 'review_id');

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
        if ($this->getReviewsCollectionSummary()) {
            foreach ($this->getReviewsCollectionSummary() as $item) {
                $identities = array_merge($identities, $item->getIdentities());
                $identities[] = \Intenso\Review\Model\Plugin\Review::CACHE_TAG . '_' . $item->getEntityPkValue();
            }
        }
        return $identities;
    }

    /**
     * Get config value for summary popover box
     *
     * @param null|string|bool|int $store
     * @return bool
     */
    public function isDisplayInExpandedMode($store = null)
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DISPLAY_IN_EXPANDED_MODE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}
