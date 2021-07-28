<?php
namespace Veriteworks\Gmo\Model\Card;

use \Magento\Framework\Exception\LocalizedException;
use \Magento\Backend\Model\Session\Quote as QuoteSession;

/**
 * Class AdminLists
 * @package Veriteworks\Gmo\Model\Card
 */
class AdminLists
{
    /**
     * @var QuoteSession
     */
    private $quoteSession;

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
     * @param QuoteSession $quote
     * @param \Veriteworks\Gmo\Gateway\ConnectorFactory $connectorFactory
     * @param \Veriteworks\Gmo\Helper\Data $gmoHelper
     * @param \Veriteworks\Gmo\Model\Member\Search $memberSearch
     */
    public function __construct(
        QuoteSession $quote,
        \Veriteworks\Gmo\Gateway\ConnectorFactory $connectorFactory,
        \Veriteworks\Gmo\Helper\Data $gmoHelper,
        \Veriteworks\Gmo\Model\Member\Search $memberSearch
    ) {
        $this->quoteSession = $quote;
        $this->gmoHelper = $gmoHelper;
        $this->connectorFactory = $connectorFactory;
        $this->memberSearch = $memberSearch;
    }

    /**
     * @param null|int $storeId
     * @return array|null
     * @throws LocalizedException
     */
    public function loadRegisteredCards($storeId = null)
    {
        $customerId = $this->quoteSession->getCustomerId();
        if (!$customerId) {
            return [];
        }

        if (!$this->memberSearch->execute($customerId, $storeId)) {
            return [];
        }

        try {
            $obj = $this->connectorFactory->create();
            $obj->setStoreId($storeId);
            $obj->setApiPath('SearchCard');
            $obj->setParam('SiteID', $this->gmoHelper->getSiteId($storeId));
            $obj->setParam('SitePass', $this->gmoHelper->getSitePassword($storeId));
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

    /**
     * @param array $subject
     * @param null $storeId
     * @return bool
     * @throws LocalizedException
     */
    public function handle(array $subject, $storeId = null)
    {
        $cards = $this->loadRegisteredCards();

        if (count($cards) > 0) {
            return true;
        }
        return false;
    }
}
