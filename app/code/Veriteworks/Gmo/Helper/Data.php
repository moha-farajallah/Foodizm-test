<?php
namespace Veriteworks\Gmo\Helper;

use \Magento\Store\Model\ScopeInterface;

/**
 * Helper class
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @param $response
     * @return bool
     */
    public function isRetryNeeded($response)
    {
        $errorCodes = explode('|', $response['ErrInfo']);
        $ignoreCodes = ['E01040010', 'E01240002', 'M01004010'];
        //$noMemberCode = 'E01390002';
        foreach ($ignoreCodes as $code) {
            if (in_array($code, $errorCodes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $response
     * @return bool
     */
    public function isIgnore($response)
    {
        $errorCodes = explode('|', $response['ErrInfo']);

        $noMemberCode = ['E01390002', 'E01240002'];

        foreach ($noMemberCode as $code) {
            if (in_array($code, $errorCodes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getShopId($storeId = null)
    {
        return $this->scopeConfig->
        getValue('veritegmo/common/shop_id', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getShopPassword($storeId = null)
    {
        return $this->scopeConfig->
        getValue('veritegmo/common/shop_password', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSiteId($storeId = null)
    {
        return $this->scopeConfig->
        getValue('veritegmo/common/site_id', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSitePassword($storeId = null)
    {
        return $this->scopeConfig->
        getValue('veritegmo/common/site_password', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getGatewayUrl($storeId = null)
    {
        return $this->scopeConfig->
        getValue('veritegmo/common/gateway_url', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getTokenUrl($storeId = null)
    {
        return $this->scopeConfig->
        getValue('veritegmo/common/token_url', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getPaymentType($storeId = null)
    {
        return $this->scopeConfig->
        getValue('payment/veritegmo_cc/payment_type', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getSplitCount($storeId = null)
    {
        return $this->scopeConfig->
        getValue('payment/veritegmo_cc/split_count', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getUseToken($storeId = null)
    {
        return $this->scopeConfig->
        getValue('payment/veritegmo_cc/use_token', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getMultiUseToken($storeId = null)
    {
        return $this->scopeConfig->
        getValue('payment/veritegmo_ccmulti/use_token', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function getRegisterCard($storeId = null)
    {
        return $this->scopeConfig->
        getValue('payment/veritegmo_cc/reg_active', ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return mixed
     */
    public function showInfoOnSuccess($storeId = null)
    {
        return $this->scopeConfig->
        getValue('veritegmo/common/show_info', ScopeInterface::SCOPE_STORE, $storeId);
    }
}
