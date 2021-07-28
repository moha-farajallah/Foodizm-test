<?php
namespace Veriteworks\Gmo\Model\Source;

/**
 * Class PaymentAction
 * @package Veriteworks\Gmo\Model\Source
 */
class PaymentAction
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
                [
                    'value' => 'authorize',
                    'label' => __('Authorize Only')
                    ],
                [
                    'value' => 'authorize_capture',
                    'label' => __('Authorize and Capture')
                    ]
                ];
    }
}
