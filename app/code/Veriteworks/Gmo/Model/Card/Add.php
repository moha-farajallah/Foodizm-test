<?php
namespace Veriteworks\Gmo\Model\Card;

use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Exception\LocalizedException;

/**
 * Class Add
 * @package Veriteworks\Gmo\Model\Card
 */
class Add
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    private $currentCustomer;

    /**
     * @var \Veriteworks\Gmo\Model\Member\Search
     */
    private $memberSearch;

    /**
     * @var \Veriteworks\Gmo\Model\Member\Register
     */
    private $memberRegister;

    /**
     * @var \Veriteworks\Gmo\Helper\Data
     */
    private $gmoHelper;

    /**
     * @var \Veriteworks\Gmo\Gateway\ConnectorFactory
     */
    private $connectorFactory;

    /**
     * Add constructor.
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Veriteworks\Gmo\Gateway\ConnectorFactory $connectorFactory
     * @param \Veriteworks\Gmo\Helper\Data $gmoHelper
     * @param \Veriteworks\Gmo\Model\Member\Search $memberSearch
     * @param \Veriteworks\Gmo\Model\Member\Register $memberRegister
     */
    public function __construct(
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Veriteworks\Gmo\Gateway\ConnectorFactory $connectorFactory,
        \Veriteworks\Gmo\Helper\Data $gmoHelper,
        \Veriteworks\Gmo\Model\Member\Search $memberSearch,
        \Veriteworks\Gmo\Model\Member\Register $memberRegister
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->gmoHelper = $gmoHelper;
        $this->connectorFactory = $connectorFactory;
        $this->memberSearch = $memberSearch;
        $this->memberRegister = $memberRegister;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     * @throws \Veriteworks\Gmo\Model\Card\LocalizedException
     */
    public function addCard(RequestInterface $request)
    {
        $customerId = $this->currentCustomer->getCustomerId();
        if (!$this->memberSearch->execute($customerId)) {
            $this->memberRegister->execute($this->currentCustomer);
        }

        $data['cc_number'] = trim($request->getParam('cc_number', null));
        $data['cc_exp_month'] = trim($request->getParam('cc_exp_month', null));
        $data['cc_exp_year'] = trim($request->getParam('cc_exp_year', null));
        $data['cc_token'] = $request->getParam('cc_token', null);

        $obj = $this->connectorFactory->create();
        $obj->setApiPath('SaveCard');
        $obj->setParam('SiteID', $this->gmoHelper->getSiteId());
        $obj->setParam('SitePass', $this->gmoHelper->getSitePassword());
        $obj->setParam('MemberID', $customerId);
        $obj->setParam('SeqMode', '1');
        $obj->setParam('DefaultFlag', 0);

        if (!$this->gmoHelper->getUseToken()) {
            $obj->setParam('CardNo', $data['cc_number']);
            $obj->setParam('Expire', sprintf("%02d", $data['cc_exp_month'])
                . substr($data['cc_exp_year'], -2));
        } else {
            $obj->setParam('Token', $data['cc_token']);
        }

        $res = $obj->execute();

        return $this->_handleRegisterResponse($res);
    }

    /**
     * @param $response
     * @return bool
     * @throws \Veriteworks\Gmo\Model\Member\LocalizedException
     */
    private function _handleRegisterResponse($response)
    {
        if (array_key_exists('ErrCode', $response) &&
            $response['ErrCode'] == 'network error'
        ) {
            throw new LocalizedException(
                __('Could not access payment gateway server. Please retry again.')
            );
        } elseif (array_key_exists('ErrCode', $response)) {
            if (array_key_exists(
                'ErrInfo',
                $response
            ) && ($this->gmoHelper->isIgnore($response))
            ) {
                return false;
            } else {
                throw new LocalizedException(__(
                    'Register card error: %1 .',
                    preg_replace('/\|/', ',', $response['ErrInfo'])
                ));
            }
        }
        return true;
    }
}
