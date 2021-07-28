<?php
namespace Veriteworks\Gmo\Model\Card;

use \Magento\Framework\Exception\LocalizedException;

/**
 * Class Lists
 * @package Veriteworks\Gmo\Model\Card
 */
class Lists
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
     * Lists constructor.
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
     * @return array|null
     * @throws \Veriteworks\Gmo\Model\Card\LocalizedException
     */
    public function loadRegisteredCards()
    {
        $customerId = $this->currentCustomer->getCustomerId();
        if (!$customerId) {
            return [];
        }

        if (!$this->memberSearch->execute($customerId)) {
            return [];
        }

        try {
            $obj = $this->connectorFactory->create();
            $obj->setApiPath('SearchCard');
            $obj->setParam('SiteID', $this->gmoHelper->getSiteId());
            $obj->setParam('SitePass', $this->gmoHelper->getSitePassword());
            $obj->setParam('MemberID', $customerId);
            $obj->setParam('SeqMode', '1');

            $res = $obj->execute();
            $this->_handleResponse($res);
        } catch (\Exception $e) {
            return [];
        }

        if (!array_key_exists('CardSeq', $res)) {
            return [];
        }
        $cardSeq     = explode('|', $res['CardSeq']);
        $defaultFlag = explode('|', $res['DefaultFlag']);
        $cardNo      = explode('|', $res['CardNo']);
        $cardName    = explode('|', $res['CardName']);
        $expire      = explode('|', $res['Expire']);
        $deleteFlag  = explode('|', $res['DeleteFlag']);
        $nums = count($cardSeq);

        $cards = [];
        if ($nums) {
            for ($i=0; $i < $nums; $i++) {
                $card = [];
                if (!$deleteFlag[$i]) {
                    $card['card_number'] = $cardNo[$i];
                    $card['card_valid_term'] = preg_replace('/^(\d\d)(\d\d)$/', '20$1/$2', $expire[$i]);
                    $card['customer_card_id'] = $cardSeq[$i];
                    $cards[] = $card;
                }
            }
        }

        return $cards;
    }

    /**
     * @param $response
     * @return bool
     * @throws \Veriteworks\Gmo\Model\Member\LocalizedException
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
