<?php
namespace Veriteworks\Gmo\Model\Source;

/**
 * Class Cctypes
 * @package Veriteworks\Gmo\Model\Source
 */
class Cctypes
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
                ['value' => 'VI','label' => __('Visa')],
                ['value' => 'MC','label' => __('Master Card')],
                ['value' => 'JCB','label' => __('JCB')],
                ['value' => 'AE','label' => __('American Express')],
                ['value' => 'DN','label' => __('Diners')]
                ];
    }
}
