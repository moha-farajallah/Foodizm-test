<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Helper;

use Magento\Customer\Model\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * Module version
     */
    const VERSION = '1.4.1';

    /**
     * Module name
     */
    const MODULE_NAME = 'Advanced Reviews';

    /**
     * Knowledge base URL
     */
    const DOCS_URL = 'https://reviews-help.getintenso.com';

    /**
     * Reviews max words preview setting config path
     */
    const XML_PATH_REVIEW_MAX_WORDS_PREVIEW = 'intenso_review/configuration/max_words_preview';

    /**
     * Number of reviews in product page config path
     */
    const XML_PATH_REVIEW_LIMIT = 'intenso_review/configuration/product_page_review_limit';

    /**
     * User-generated photos
     */
    const XML_PATH_REVIEW_PHOTOS = 'intenso_review/configuration/user_generated_photos';

    /**
     * Static block after review form config path
     */
    const REVIEW_STATIC_BLOCK = 'intenso_review/configuration/static_block';

    /**
     * Customer sender email config path
     */
    const XML_PATH_CUSTOMER_EMAIL_SENDER = 'intenso_review/customer_email_options/sender_email_identity';

    /**
     * Owner sender email config path
     */
    const XML_PATH_OWNER_EMAIL_SENDER = 'intenso_review/owner_email_options/sender_email_identity';

    /**
     * Recipient email config path
     */
    const XML_PATH_RECIPIENT_EMAIL = 'intenso_review/owner_email_options/recipient_email';

    /**
     * Store owner display name
     */
    const XML_PATH_STORE_OWNER_DISPLAY_NAME = 'intenso_review/store_owner_comments/name';

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * Rating filters
     */
    private $availableFilters = [
        1 => \Intenso\Review\Block\Html\Pager::ONE_STAR_FILTER,
        2 => \Intenso\Review\Block\Html\Pager::TWO_STARS_FILTER,
        3 => \Intenso\Review\Block\Html\Pager::THREE_STARS_FILTER,
        4 => \Intenso\Review\Block\Html\Pager::FOUR_STARS_FILTER,
        5 => \Intenso\Review\Block\Html\Pager::FIVE_STARS_FILTER,
    ];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
        $this->httpContext = $httpContext;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Returns module version
     *
     * @return string
     */
    public function getVersion()
    {
        return self::VERSION;
    }

    /**
     * Returns module name
     *
     * @return string
     */
    public function getModuleName()
    {
        return self::MODULE_NAME;
    }

    /**
     * Returns knowledge base URL
     *
     * @return string
     */
    public function getDocsUrl()
    {
        return self::DOCS_URL;
    }

    /**
     * Returns max number of words for review's preview
     *
     * @param null|string|bool|int $store
     * @return int
     */
    public function getNumberOfWords($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REVIEW_MAX_WORDS_PREVIEW,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns number of reviews to be shown on product page
     *
     * @param null|string|bool|int $store
     * @return int
     */
    public function getNumReviewsForProductPage($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REVIEW_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check whether users can upload photos alongside with their reviews
     *
     * @param null|string|bool|int $store
     * @return int
     */
    public function canUploadPhotos($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_REVIEW_PHOTOS,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Returns decoded cookie
     *
     * @param string $cookieName
     * @param bool $decode
     * @return bool|string
     */
    public function getCookie($cookieName, $decode = false)
    {
        if ($cookie = $this->cookieManager->getCookie($cookieName)) {
            if ($decode) {
                $cookie = base64_decode($cookie);
            }
            return $cookie;
        } else {
            return false;
        }
    }

    /**
     * Sets encoded cookie
     *
     * @param string $cookieName
     * @param mixed $value
     * @param int $duration
     * @param bool $encode
     * @return bool
     */
    public function setCookie($cookieName, $value, $duration, $encode = true)
    {
        if ($encode) {
            $value = base64_encode($value);
        }
        $publicCookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setDuration($duration)
            ->setPath('/')
            ->setHttpOnly(false);
        $this->cookieManager->setPublicCookie($cookieName, $value, $publicCookieMetadata);
        return true;
    }

    /**
     * Get filter query string
     *
     * @param int $stars
     * @return string
     */
    public function getFilterQuerystring($stars)
    {
        if (isset($this->availableFilters[$stars])) {
            return '?' . \Intenso\Review\Block\Html\Pager::FILTER_VAR_NAME . '=' . $this->availableFilters[$stars];
        }
    }

    /**
     * Returns static block identifier
     *
     * @param null|string|bool|int $store
     * @return string
     */
    public function getStaticBlock($store = null)
    {
        return $this->scopeConfig->getValue(
            self::REVIEW_STATIC_BLOCK,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get review statuses with their codes
     *
     * @return array
     */
    public function getReviewStatuses()
    {
        $statuses = [
            \Intenso\Review\Model\Review::STATUS_APPROVED => __('Approved'),
            \Intenso\Review\Model\Review::STATUS_PENDING => __('Pending'),
            \Intenso\Review\Model\Review::STATUS_NOT_APPROVED => __('Not Approved'),
            \Intenso\Review\Model\Review::STATUS_SPAM => __('Spam')
        ];
        return $statuses;
    }

    /**
     * Get review statuses as option array
     *
     * @return array
     */
    public function getReviewStatusesOptionArray()
    {
        $result = [];
        foreach ($this->getReviewStatuses() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    /**
     * Get MAP statuses with their codes
     *
     * @return array
     */
    public function getMapStatuses()
    {
        $statuses = [
            \Intenso\Review\Model\Map::STATUS_NOTSENT => __('Queued'),
            \Intenso\Review\Model\Map::STATUS_SENT => __('Sent')
        ];
        return $statuses;
    }

    /**
     * Get MAP review posted statuses with their codes
     *
     * @return array
     */
    public function getMapReviewPosted()
    {
        $statuses = [
            \Intenso\Review\Model\Map::REVIEW_POSTED => __('Yes'),
            \Intenso\Review\Model\Map::REVIEW_NOT_POSTED => __('No')
        ];
        return $statuses;
    }

    /**
     * Send email to customer
     *
     * @param string $template
     * @param string $recipient
     * @param array $templateVars
     * @param int/null $storeId
     * @return void
     */
    public function sendMailToCustomer($template, $recipient, $templateVars, $storeId = null)
    {
        if (is_null($storeId)) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFrom($this->scopeConfig->getValue(self::XML_PATH_CUSTOMER_EMAIL_SENDER, $storeScope))
            ->addTo($recipient)
            ->getTransport();

            $transport->sendMessage();
            return;
        } catch (\Exception $e) {
            $this->_logger->notice($e->getMessage());
            return;
        }
    }

    /**
     * Send email to store owner
     *
     * @param string $template
     * @param array $templateVars
     * @return void
     */
    public function sendMailToOwner($template, $templateVars)
    {
        try {
            $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
            $transport = $this->transportBuilder->setTemplateIdentifier(
                $template
            )->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $this->storeManager->getStore()->getId()
                ]
            )
            ->setTemplateVars($templateVars)
            ->setFrom($this->scopeConfig->getValue(self::XML_PATH_OWNER_EMAIL_SENDER, $storeScope))
            ->addTo($this->scopeConfig->getValue(self::XML_PATH_RECIPIENT_EMAIL, $storeScope))
            ->getTransport();

            $transport->sendMessage();
            return;
        } catch (\Exception $e) {
            $this->_logger->notice($e->getMessage());
            return;
        }
    }

    /**
     * Review images post action
     *
     * @return string
     */
    public function imgPostAction()
    {
        return $this->_urlBuilder->getUrl(
            'intenso_review/image/post',
            [
                '_secure' => true
            ]
        );
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }

    /**
     * Get the display name of the store owner from the config
     *
     * @return string
     */
    public function getStoreOwnerDisplayName()
    {
        $storeId = $this->storeManager->getStore()->getId();

        $storeName = $this->scopeConfig->getValue(
            self::XML_PATH_STORE_OWNER_DISPLAY_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (!$storeName) {
            $storeName = $this->storeManager->getStore()->getFrontendName();
        }

        return $storeName;
    }
}
