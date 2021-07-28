<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
namespace Intenso\Review\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Intenso\Review\Model\User as UserData;
use Intenso\Review\Model\Vote as CustomerVote;

/**
 * Customer reviews controller
 */
abstract class Vote extends Action
{
    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * User data model
     *
     * @var \Intenso\Review\Model\User
     */
    protected $userData;

    /**
     * User data model
     *
     * @var \Intenso\Review\Model\Vote
     */
    protected $vote;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Intenso\Review\Model\User $userData
     * @param CustomerVote $vote
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        UserData $userData,
        CustomerVote $vote
    ) {
        $this->customerSession = $customerSession;
        $this->userData = $userData;
        $this->vote = $vote;
        parent::__construct($context);
    }
}
