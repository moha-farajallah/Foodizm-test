<?php


namespace Veriteworks\Gmo\Model\Member;

use \Magento\Customer\Helper\Session\CurrentCustomer;
use \Magento\Framework\Exception\LocalizedException;

/**
 * Class Register
 * @package Veriteworks\Gmo\Model\Member
 */
class Register
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
     * @param CurrentCustomer $currentCustomer
     * @return bool
     * @throws LocalizedException
     */
    public function execute(CurrentCustomer $currentCustomer)
    {
        $customer = $currentCustomer->getCustomer();
        $name = $customer->getLastname() . $customer->getFirstname();

        $obj = $this->connectorFactory->create();
        $obj->setApiPath('SaveMember');
        $obj->setParam('SiteID', $this->gmoHelper->getSiteId());
        $obj->setParam('SitePass', $this->gmoHelper->getSitePassword());
        $obj->setParam('MemberID', $customer->getId());
        $obj->setParam('MemberName', $name);

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
            $response['ErrCode'] == 'network error'
        ) {
            throw new LocalizedException(
                __('Could not access payment gateway server. Please retry again.')
            );
        } elseif (array_key_exists('ErrCode', $response)) {
            if (array_key_exists('ErrInfo', $response)
                && ($this->gmoHelper->isIgnore($response))
            ) {
                return false;
            } else {
                throw new LocalizedException(__(
                    'Registration error: %1 .',
                    preg_replace('/\|/', ',', $response['ErrInfo'])
                ));
            }
        }
        return true;
    }
}
