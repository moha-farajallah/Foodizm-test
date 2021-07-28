<?php
namespace Veriteworks\Gmo\Gateway;

use \Magento\Store\Model\ScopeInterface;
use \Magento\Framework\Exception\LocalizedException;
use \Laminas\Http\Client;

/**
 * Http request connection class
 */
class Connector
{
    /**
     * @var int
     *
     * Retry Counter
     */
    protected $_retryCount = 0;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;

    /**
     * @var \Zend_Http_Client_Adapter_Interface
     */
    protected $_adapter;

    /**
     * @var array
     */
    protected $_data = [];

    /**
     * @var string
     */
    protected $_apiPath;

    /**
     * @var string
     */
    protected $_charset = 'EUC-JP';

    /**
     * @var string
     */
    protected $_charset2 = 'SJIS';

    private $storeId = null;

    /**
     * Connector constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Zend_Http_Client_Adapter_Interface $adapter
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        \Zend_Http_Client_Adapter_Interface $adapter
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_adapter = $adapter;
    }

    /**
     * @param $path
     */
    public function setApiPath($path)
    {
        $gateway = $this->_getConfig('gateway_url');
        $this->_apiPath = $gateway . 'payment/' . $path . '.idPass';

        return $this;
    }

    /**
     * @return string
     */
    public function getApiPath()
    {
        return $this->_apiPath;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $returnValue = [];

        $config = [
            'adapter'      => $this->_adapter,
        ];

        $client = new \Zend_Http_Client($this->_apiPath, $config);

        foreach ($this->_data as $key => $value) {
            if ($key == 'TdTenantName') {
                $value = mb_convert_encoding($value, $this->_charset, 'UTF-8');
                $value = base64_encode($value);
                $client->setParameterPost($key, $value);
            } else {
                $value = mb_convert_encoding($value, $this->_charset2, 'UTF-8');
                $client->setParameterPost($key, $value);
            }

            //$this->_log($key . ":" . $value);
        }

        try {
            $response = $client->request('POST');

            if ($response->isError()) {
                if ($this->_retryCount < 3) {
                    $this->_retryCount++;
                    $this->execute();
                } else {
                    $returnValue['ErrCode'] = 'network error';
                }
            } else {
                $this->_log(var_export($response, true));
                $returnValue = $this->_parseResponseBody($response->getBody());
            }
        } catch (\Exception $e) {
            if ($this->_retryCount < 3) {
                $this->_retryCount++;
                $this->execute();
            } else {
                $returnValue['ErrCode'] = 'network error';
            }
        }
        return $returnValue;
    }

    /**
     * @param $key
     * @param $param
     * @return $this
     */
    public function setParam($key, $param)
    {
        $this->_data[$key] = $param;
        return $this;
    }

    /**
     * @param $key
     * @return null
     */
    public function getParam($key)
    {
        if (array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        }

        return null;
    }

    /**
     * @return \Zend_Http_Client_Adapter_Interface
     */
    public function getAdapter()
    {
        return $this->_adapter;
    }

    /**
     * @param $charset
     * @return $this
     */
    public function setCharset($charset)
    {
        $this->_charset = $charset;
        return $this;
    }

    /**
     * @param $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * @param $str
     */
    protected function _log($str)
    {
        $this->_logger->info($str);
    }

    /**
     * @param $key
     * @return mixed
     */
    protected function _getConfig($key)
    {
        $key = 'veritegmo/common/' . $key;
        return $this->_scopeConfig->getValue($key, ScopeInterface::SCOPE_STORE, $this->storeId);
    }

    /**
     * @param $body
     * @return array
     */
    protected function _parseResponseBody($body)
    {
        $_results = preg_split("/&/", $body);
        $returnValue = [];

        foreach ($_results as $data) {
            if (preg_match('/=/', $data)) {
                list($key, $value) = explode('=', $data, 2);
                $returnValue[$key] = trim($value);
            }
        }

        return $returnValue;
    }
}
