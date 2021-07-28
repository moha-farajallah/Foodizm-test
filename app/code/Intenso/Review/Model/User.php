<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Model;

/**
 * User model
 */
class User extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Name of cookie that holds private content version
     */
    const COOKIE_NAME = 'mage-review';

    /**
     * Ten years cookie period
     */
    const COOKIE_PERIOD = 315360000;

    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $cookieManager;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    protected $httpHeader;

    /**
     * @var \Intenso\Review\Helper\Data
     */
    protected $intensoHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\HTTP\Header $httpHeader
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Intenso\Review\Helper\Data $intensoHelper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\HTTP\Header $httpHeader,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Intenso\Review\Helper\Data $intensoHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->cookieManager = $cookieManager;
        $this->httpHeader = $httpHeader;
        $this->intensoHelper = $intensoHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Intenso\Review\Model\ResourceModel\User');
    }

    /**
     * Identify user and set a cookie
     *
     * @return \Intenso\Review\Model\User
     */
    public function identify()
    {
        $data = [
            'ip' => $this->remoteAddress->getRemoteAddress(),
            'http_user_agent' => $this->httpHeader->getHttpUserAgent(),
            'cookie' => $this->intensoHelper->getCookie('magereviews', true),
        ];
        $newCookieCode = $this->generateCookieCode($data['ip'] . $data['http_user_agent']);

        // check if user has a review cookie
        if ($data['cookie']) {
            $this->load($data['cookie'], 'cookie');
            if ($this->getIp() != $data['ip'] || $this->getHttpUserAgent() != $data['http_user_agent']) {
                $data['cookie'] = $newCookieCode;
                $this->setData($data)->save();
            }
        } else {
            // check if IP and HTTP user agent are stored in database
            $collection = $this->getCollection()->getUserByIpAndUserAgent($data['ip'], $data['http_user_agent']);
            $user = $collection->getLastItem();
            if ($user && $user->getId()) {
                $this->setData($user->getData());
            } else {
                $data['cookie'] = $newCookieCode;
                $this->setData($data)->save();
            }
        }

        // create or update cookie
        $this->intensoHelper->setCookie(self::COOKIE_NAME, '$this->getCookie()', self::COOKIE_PERIOD, true);

        return $this;
    }

    /**
     * Generate hash for cookie
     *
     * @param string $salt
     * @return string
     */
    private function generateCookieCode($salt = '')
    {
        return hash('sha256', time() . $salt);
    }

    /**
     * Get remote IP
     *
     * @return string
     */
    public function getRemoteAddress()
    {
        return $this->remoteAddress->getRemoteAddress();
    }

    /**
     * Get user agent
     *
     * @return string
     */
    public function getHttpUserAgent()
    {
        return $this->httpHeader->getHttpUserAgent();
    }
}
