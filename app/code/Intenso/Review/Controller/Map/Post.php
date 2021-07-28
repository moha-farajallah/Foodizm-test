<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Map;

use Intenso\Review\Model\Review;
use Magento\Framework\Controller\ResultFactory;
use Magento\Review\Controller\Product as ProductController;

class Post extends ProductController
{
    /**
     * Moderate customer review config path
     */
    const XML_PATH_MODERATE_CUSTOMER_REVIEW = 'intenso_review/configuration/moderate_customer_review';

    /**
     * Enable customer notification config path
     */
    const XML_PATH_SEND_CUSTOMER_NOTIFICATION = 'intenso_review/customer_email_options/enable_review_published_notification';

    /**
     * Customer notification template config path
     */
    const XML_PATH_CUSTOMER_NOTIFICATION_TEMPLATE = 'intenso_review/customer_email_options/review_published_template';

    /**
     * Enable owner notification config path
     */
    const XML_PATH_SEND_OWNER_NOTIFICATION = 'intenso_review/owner_email_options/enable_new_review_notification';

    /**
     * Owner notification template config path
     */
    const XML_PATH_OWNER_NOTIFICATION_TEMPLATE = 'intenso_review/owner_email_options/new_review_template';

    /**
     * Application Cache Manager
     *
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cacheManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * User data
     *
     * @var \Intenso\Review\Model\User
     */
    protected $userData;

    /**
     * Mail after purchase data
     *
     * @var \Intenso\Review\Model\MapFactory
     */
    protected $mapFactory;

    /**
     * Review data
     *
     * @var \Intenso\Review\Helper\Data
     */
    protected $reviewData = null;

    /**
     * Akismet helper
     *
     * @var \Intenso\Review\Helper\Akismet
     */
    protected $akismetHelper = null;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    protected $backendUrl;

    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory
     */
    protected $ratingCollectionF;

    /**
     * Core date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Intenso\Review\Model\Images\Processor
     */
    protected $imageProcessor;

    /**
     * @var \Intenso\Review\Model\ImageFactory
     */
    protected $imageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\CacheInterface $cacheManager
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Review\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Catalog\Model\Design $catalogDesign
     * @param \Magento\Framework\Session\Generic $reviewSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Intenso\Review\Model\User $userData
     * @param \Intenso\Review\Helper\Data $reviewData
     * @param \Intenso\Review\Helper\Akismet $akismetHelper
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param \Magento\Backend\Model\UrlInterface $backendUrl
     * @param \Intenso\Review\Model\MapFactory $mapFactory
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $ratingCollectionF
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Intenso\Review\Model\Images\Processor $imageProcessor
     * @param \Intenso\Review\Model\ImageFactory $imageFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\CacheInterface $cacheManager,
        \Magento\Framework\Registry $registry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Catalog\Model\Design $catalogDesign,
        \Magento\Framework\Session\Generic $reviewSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Intenso\Review\Model\User $userData,
        \Intenso\Review\Helper\Data $reviewData,
        \Intenso\Review\Helper\Akismet $akismetHelper,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Magento\Backend\Model\UrlInterface $backendUrl,
        \Intenso\Review\Model\MapFactory $mapFactory,
        \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $ratingCollectionF,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Intenso\Review\Model\Images\Processor $imageProcessor,
        \Intenso\Review\Model\ImageFactory $imageFactory
    ) {
        parent::__construct(
            $context,
            $registry,
            $customerSession,
            $categoryRepository,
            $logger,
            $productRepository,
            $reviewFactory,
            $ratingFactory,
            $catalogDesign,
            $reviewSession,
            $storeManager,
            $formKeyValidator
        );
        $this->cacheManager = $cacheManager;
        $this->scopeConfig = $scopeConfig;
        $this->userData = $userData;
        $this->reviewData = $reviewData;
        $this->akismetHelper = $akismetHelper;
        $this->mapFactory = $mapFactory;
        $this->localeResolver = $localeResolver;
        $this->backendUrl = $backendUrl;
        $this->ratingCollectionF = $ratingCollectionF;
        $this->date = $date;
        $this->orderFactory = $orderFactory;
        $this->registry = $registry;
        $this->imageProcessor = $imageProcessor;
        $this->imageFactory = $imageFactory;
        $this->logger = $logger;
    }

    /**
     * Submit MAP action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $storeId = $this->getRequest()->getParam('store') ?
            $this->getRequest()->getParam('store') : $this->storeManager->getStore()->getId();
        $storeCode = $this->storeManager->getStore($storeId)->getCode();
        $url = $this->_url->getUrl('intenso_review/*/success/', []) . '?___store=' . $storeCode;

        $data = [];
        $ratingData = [];
        $ratingValue = $this->getRequest()->getParam('rating');

        if (is_array($ratingValue)) {
            $data['rating'] = $ratingData = $ratingValue;
        } else {
            $ratingValue = intval($ratingValue);
            if ($ratingValue < 1 || $ratingValue > 5) {
                $ratingValue = 5;
            }
            $ratings = $this->getRatings();
            foreach ($ratings as $rating) {
                $ratingId = $rating->getRatingId();
                $optionId = $this->ratingCollectionF->create()->addRatingFilter(
                    $ratingId
                )->getItemByColumnValue('value', $ratingValue)->getOptionId();
                $ratingData[$ratingId] = $optionId;
            }
            $data['rating'] = $ratingData;
        }

        $data['token'] = $this->getRequest()->getParam('token');
        $data['title'] = $this->getRequest()->getParam('title');
        $data['detail'] = $this->getRequest()->getParam('detail');
        $data['image'] = $this->getRequest()->getParam('image');
        $submit = $this->getRequest()->getParam('submit');
        $mapFactory = $this->mapFactory->create()->load($data['token'], 'customer_token');

        if (!$mapFactory->getId() && $data['token'] != 'test') {
            // incorrect token - redirect to no-route page
            $resultForward->forward('noroute');
            return $resultForward;
        }

        if ($mapFactory->getReviewPosted()) {
            return $resultRedirect->setUrl($url);
        }

        if ($submit && !empty($data['title']) && !empty($data['detail'])) {
            if ($data['token'] == 'test') {
                return $resultRedirect->setUrl($url);
            }

            $product = $this->productRepository->getById($mapFactory->getProductId());
            $order = $this->orderFactory->create()->load($mapFactory->getOrderId());
            if ($order->getCustomerFirstname()) {
                $data['nickname'] = $order->getCustomerFirstname() . ' ' .
                    mb_strimwidth($order->getCustomerLastname(), 0, 2, '.');
            } else {
                $data['nickname'] = $order->getBillingAddress()->getFirstname() . ' ' .
                    mb_strimwidth($order->getBillingAddress()->getLastname(), 0, 2, '.');
            }
            $data['email'] = $order->getCustomerEmail();

            /** @var \Magento\Review\Model\Review $review */
            $review = $this->reviewFactory->create()->setData($data);
            $review->unsetData('review_id');

            try {
                $moderateCustomer = $this->scopeConfig->getValue(
                    self::XML_PATH_MODERATE_CUSTOMER_REVIEW,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                $customerNotification = $this->scopeConfig->getValue(
                    self::XML_PATH_SEND_CUSTOMER_NOTIFICATION,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );
                $ownerNotification = $this->scopeConfig->getValue(
                    self::XML_PATH_SEND_OWNER_NOTIFICATION,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                );

                if ($moderateCustomer) {
                    $status = Review::STATUS_PENDING;
                } else {
                    $status = Review::STATUS_APPROVED;
                }

                $review->setUserIp($this->userData->getRemoteAddress());
                $review->setUserAgent($this->userData->getHttpUserAgent());
                // flag if comment is spam
                $akismetArray = [
                    'blog'                 => $this->storeManager->getStore()->getBaseUrl(),
                    'user_ip'              => $review->getUserIp(),
                    'user_agent'           => $review->getUserAgent(),
                    'comment_type'         => 'comment',
                    'comment_author'       => $review->getNickname(),
                    'comment_author_email' => $data['email'],
                    'comment_content'      => $review->getDetail(),
                    'blog_lang'            => $this->localeResolver->getLocale()
                ];
                if ($this->akismetHelper->isSpam($akismetArray)) {
                    $status = \Intenso\Review\Model\Review::STATUS_SPAM;
                }

                $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE))
                    ->setEntityPkValue($product->getId())
                    ->setStatusId($status)
                    ->setCustomerId($order->getCustomerId())
                    ->setStoreId($this->storeManager->getStore()->getId())
                    ->setStores([$this->storeManager->getStore()->getId()])
                    ->save();

                foreach ($ratingData as $ratingId => $optionId) {
                    $this->ratingFactory->create()
                        ->setRatingId($ratingId)
                        ->setReviewId($review->getId())
                        ->setCustomerId($order->getCustomerId())
                        ->addOptionVote($optionId, $product->getId());
                }

                $review->aggregate();

                $this->registry->register('is_verified_purchase', true);

                // save review photos
                if (isset($data['image'])) {
                    foreach ($data['image'] as $file) {
                        $renamedFile = $this->imageProcessor->duplicateImageFromTmp($file);
                        $this->imageFactory->create()
                            ->setReviewId($review->getId())
                            ->setFile($renamedFile)
                            ->save();
                    }
                }

                // Send email to customer
                if ($status == Review::STATUS_APPROVED && $customerNotification) {
                    $review->setCustomerEmail($data['email']);
                    $review->setProductName($product->getName());
                    $this->reviewData->sendMailToCustomer(
                        $this->scopeConfig->getValue(self::XML_PATH_CUSTOMER_NOTIFICATION_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
                        $data['email'],
                        ['review' => $review, 'store' => $this->storeManager->getStore($storeId)],
                        $storeId
                    );
                }

                // Send email to store owner
                if ($status != Review::STATUS_SPAM && $ownerNotification) {
                    $review->setCustomerEmail($data['email']);
                    $review->setProductName($product->getName());
                    $statuses = $this->reviewData->getReviewStatuses();
                    $status = isset($statuses[$status]) ? $statuses[$status] : '';
                    $review->setStatus($status);
                    $review->setEditReviewUrl($this->backendUrl->getUrl('review/product/edit', ['id' => $review->getId()]));
                    $this->reviewData->sendMailToOwner(
                        $this->scopeConfig->getValue(self::XML_PATH_OWNER_NOTIFICATION_TEMPLATE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId),
                        ['review' => $review, 'store' => $this->storeManager->getStore()]
                    );
                }

                $mapFactory->setReviewPosted(1)
                    ->setReviewDate($this->date->gmtDate())
                    ->save();

                $this->cleanCache($product->getId());

                return $resultRedirect->setUrl($url);
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving your review.'));
            }
        }

        $this->registry->register('map_form_data', $data);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        return $resultPage;
    }

    /**
     * Get collection of ratings
     *
     * @return \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory
     */
    public function getRatings()
    {
        return $this->ratingFactory->create()->getResourceCollection()->addEntityFilter(
            'product'
        )->setPositionOrder()->addRatingPerStoreName(
            $this->storeManager->getStore()->getId()
        )->setStoreFilter(
            $this->storeManager->getStore()->getId()
        )->setActiveFilter(
            true
        )->load()->addOptionToItems();
    }

    /**
     * Clear cache related with product id
     *
     * @param int $productId
     * @return $this
     */
    protected function cleanCache($productId)
    {
        $this->cacheManager->clean(\Intenso\Review\Model\Plugin\Review::CACHE_TAG . '_' . $productId);
        return $this;
    }
}
