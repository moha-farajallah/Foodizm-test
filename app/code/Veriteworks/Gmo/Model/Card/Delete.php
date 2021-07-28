<?php
namespace Veriteworks\Gmo\Model\Card;

use \Magento\Framework\App\RequestInterface;
use \Magento\Framework\Exception\LocalizedException;

/**
 * Class Delete
 * @package Veriteworks\Gmo\Model\Card
 */
class Delete
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
     */
    public function __construct(
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Veriteworks\Gmo\Gateway\ConnectorFactory $connectorFactory,
        \Veriteworks\Gmo\Helper\Data $gmoHelper,
        \Veriteworks\Gmo\Model\Member\Search $memberSearch
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->gmoHelper = $gmoHelper;
        $this->connectorFactory = $connectorFactory;
        $this->memberSearch = $memberSearch;
    }

    /**
     * @param RequestInterface $request
     * @return bool
     * @throws \Veriteworks\Gmo\Model\Card\LocalizedException
     */
    public function deleteCard(RequestInterface $request)
    {
        $customerId = $this->currentCustomer->getCustomerId();
        if (!$this->memberSearch->execute($customerId)) {
            return false;
        }

        $cardId = $request->getParam('id', null);

        $obj = $this->connectorFactory->create();
        $obj->setApiPath('DeleteCard');
        $obj->setParam('SiteID', $this->gmoHelper->getSiteId());
        $obj->setParam('SitePass', $this->gmoHelper->getSitePassword());
        $obj->setParam('MemberID', $customerId);
        $obj->setParam('SeqMode', '1');
        $obj->setParam('CardSeq', $cardId);

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
