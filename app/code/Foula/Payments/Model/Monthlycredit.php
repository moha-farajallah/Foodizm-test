<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Foula\Payments\Model;

/**
 * Class Checkmo
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 *
 * @api
 * @since 100.0.2
 */
class Monthlycredit extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'foula_monthlycredit';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::CODE;
    
    /**
     * Availability option
     *
     * @var bool
     */
    protected $_isOffline = true;


    /**
     * Payment block paths
     *
     * @var string
     */
    protected $_formBlockType = \Foula\Payments\Block\Form\Monthlycredit::class;

}
