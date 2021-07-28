<?php


namespace Veriteworks\Gmo\Model\Member;

use \Magento\Framework\Exception\LocalizedException;

/**
 * Class Search
 * @package Veriteworks\Gmo\Model\Member
 */
class Search
{

    /**
     * @var \Veriteworks\Gmo\Gateway\ConnectorFactory
     */
    private $connectorFactory;

    /**
     * @var \Veriteworks\Gmo\Helper\Data
     */
    private $gmoHelper;

    /**
     * Search constructor.
     * @param \Veriteworks\Gmo\Gateway\ConnectorFactory $connectorFactory
     * @param \Veriteworks\Gmo\Helper\Data $gmoHelper
     */
    public function __construct(
        \Veriteworks\Gmo\Gateway\ConnectorFactory $connectorFactory,
        \Veriteworks\Gmo\Helper\Data $gmoHelper
    ) {
        $this->connectorFactory = $connectorFactory;
        $this->gmoHelper = $gmoHelper;
    }

    /**
     * @param int $customerId
     * @param int|null $storeId
     * @return bool
     * @throws LocalizedException
     */
    public function execute($customerId, $storeId = null)
    {
        $obj = $this->connectorFactory->create();
        $obj->setStoreId($storeId);
        $obj->setApiPath('SearchMember');
        $obj->setParam('SiteID', $this->gmoHelper->getSiteId($storeId));
        $obj->setParam('SitePass', $this->gmoHelper->getSitePassword($storeId));
        $obj->setParam('MemberID', $customerId);

        $result = $obj->execute();

        return $this->_handleResponse($result);
    }

    /**
     * @param $response
     * @return bool
     * @throws LocalizedException
     */
    private function _handleResponse($response)
    {
        if (array_key_exists('ErrCode', $response) &&
            $response['ErrCode'] == 'network error') {
            throw new LocalizedException(__('Could not access payment gateway server. Please retry again.'));
        } elseif (array_key_exists('ErrCode', $response)) {
            if (array_key_exists('ErrInfo', $response) && ($this->gmoHelper->isIgnore($response))) {
                return false;
            } else {
                throw new LocalizedException(__(
                    'Search error: %1 .',
                    preg_replace('/\|/', ',', $response['ErrInfo'])
                ));
            }
        }
        return true;
    }
}
