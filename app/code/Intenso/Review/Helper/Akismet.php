<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Helper;

use Magento\Framework\DataObject;
use Magento\Store\Model\ScopeInterface;

class Akismet extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Akismet enable config path
     */
    const AKISMET_ENABLE = 'intenso_review/akismet/enable';

    /**
     * Akismet API key config path
     */
    const AKISMET_API_KEY = 'intenso_review/akismet/api_key';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\HTTP\ZendClientFactory
     */
    protected $httpClientFactory;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory
    ) {
        $this->storeManager = $storeManager;
        $this->httpClientFactory = $httpClientFactory;
        parent::__construct($context);
    }

    /**
     * Check Akismet API key
     *
     * @param string $key
     * @return DataObject
     */
    public function checkAkismetApiKey($key)
    {
        // Default response
        $gatewayResponse = new DataObject([
            'is_valid' => false,
            'request_message' => __('Error during Akismet API Key verification.'),
        ]);

        if ($this->verifyKey($key, $this->storeManager->getStore()->getBaseUrl())) {
            $gatewayResponse->setIsValid(true);
            $gatewayResponse->setRequestMessage(__('The API Key is valid.'));
        } else {
            $gatewayResponse->setRequestMessage(__('Please enter a valid API Key.'));
        }
        return $gatewayResponse;
    }

    /**
     * Check is Akismet spam filter is enabled in config
     *
     * @param null|string|bool|int $store
     * @return bool
     */
    private function isAkismetEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::AKISMET_ENABLE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Akismet API key
     *
     * @param null|string|bool|int $store
     * @return string
     */
    private function getAkismetApiKey($store = null)
    {
        return $this->scopeConfig->getValue(
            self::AKISMET_API_KEY,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check content for spam
     *
     * @param array $data
     * @return bool
     */
    public function isSpam($data)
    {
        if (!$this->isAkismetEnabled()) {
            return false;
        }

        $key = $this->getAkismetApiKey();

        try {
            $response = $this->_makeApiCall('/1.1/comment-check', $data, $key);

            $return = trim($response->getBody());

            if ('invalid' == $return) {
                $this->_logger->notice('Invalid Akismet API key');
                return false;
            }

            if ('true' == $return) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            $this->_logger->notice($e->getMessage());
        }
        return false;
    }

    /**
     * Submission of false positives - items that were incorrectly classified as spam by Akismet
     *
     * @param array $data
     * @return void
     */
    public function submitHam($data)
    {
        if (!$this->isAkismetEnabled()) {
            return;
        }

        $key = $this->getAkismetApiKey();

        try {
            $this->_makeApiCall('/1.1/submit-ham', $data, $key);
        } catch (\Exception $e) {
            $this->_logger->notice($e->getMessage());
        }
    }

    /**
     * Submission of spam not filtered by Akismet
     *
     * @param array $data
     * @return void
     */
    public function submitSpam($data)
    {
        if (!$this->isAkismetEnabled()) {
            return;
        }

        $key = $this->getAkismetApiKey();

        try {
            $response = $this->_makeApiCall('/1.1/submit-spam', $data, $key);
            $value    = trim($response->getBody());
            if ('invalid' == $value) {
                $this->_logger->notice('Invalid Akismet API key');
            }
        } catch (\Exception $e) {
            $this->_logger->notice($e->getMessage());
        }
    }

    /**
     * Verify an API key
     *
     * @param string $key Optional; API key to verify
     * @param string $blog Optional; blog URL against which to verify key
     * @return boolean
     */
    public function verifyKey($key = null, $blog = null)
    {
        $response = $this->post('rest.akismet.com', '/1.1/verify-key', [
            'key'  => $key,
            'blog' => $blog
        ]);

        return ('valid' == $response->getBody());
    }

    /**
     * Post a request
     *
     * @param string $host
     * @param string $path
     * @param array  $params
     * @return mixed
     */
    protected function post($host, $path, array $params)
    {
        $uri    = 'http://' . $host . ':80' . $path;
        $client = $this->httpClientFactory->create();
        $client->setUri($uri);
        $client->setConfig([
            'useragent'    => 'Zend Framework/1.12.16 | Akismet/1.11',
        ]);

        $client->setHeaders([
            'Host'         => $host,
            'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8'
        ]);
        $client->setParameterPost($params);

        $client->setMethod(\Zend_Http_Client::POST);
        return $client->request();
    }

    /**
     * Perform an API call
     *
     * @param string $path
     * @param array $params
     * @param string $key
     * @return \Zend_Http_Response|bool
     */
    protected function _makeApiCall($path, $params, $key)
    {
        if (empty($params['user_ip']) || empty($params['user_agent'])) {
            $this->_logger->notice('Missing required Akismet fields (user_ip and user_agent are required)');
            return false;
        }

        if (!isset($params['blog'])) {
            $params['blog'] = $this->storeManager->getStore()->getBaseUrl();
        }

        return $this->post($key . '.rest.akismet.com', $path, $params);
    }
}
