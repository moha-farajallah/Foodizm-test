<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Foula\Payments\Block\Form;

/**
 * Block for payment method form
 */
class Monthlycredit extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * template
     *
     * @var string
     */
    protected $_template = 'Foula_Payments::payments/monthlycredit_instructions.phtml';
}
