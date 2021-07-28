<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Vote;

use Magento\Framework\Controller\ResultFactory;

class Post extends \Intenso\Review\Controller\Vote
{
    /**
     * Submit new vote action
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $responseContent = [
            'vote' => 'error',
        ];
        if ($this->getRequest()->isPost()) {
            $customerId = null;
            $guestId = null;
            $helpful = ($this->getRequest()->getParam('helpful') == 'true') ? 1 : 0;
            if ($this->customerSession->isLoggedIn()) {
                $customerId = $this->customerSession->getCustomerId();
            }

            $guest = $this->userData->identify();
            if ($guest->getId()) {
                $guestId = $guest->getId();
            }

            if ($customerId || $guestId) {
                $voteData = [
                    'customer_id' => $customerId,
                    'guest_id' => $guestId,
                    'review_id' => (int) $this->getRequest()->getParam('id'),
                    'helpful' => (int) $helpful
                ];
                $canVote = $this->vote->canVote($voteData);
                if ($canVote == \Intenso\Review\Model\ResourceModel\Vote::CAN_VOTE) {
                    $this->vote->setData($voteData)->save();
                    $responseContent['vote'] = 'success';
                } elseif ($canVote == \Intenso\Review\Model\ResourceModel\Vote::ALREADY_VOTED) {
                    $responseContent['vote'] = 'duplicated';
                }
            }
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
