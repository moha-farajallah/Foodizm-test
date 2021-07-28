<?php
namespace Veriteworks\Gmo\Model\Source;

/**
 * Class Paymenttype
 * @package Veriteworks\Gmo\Model\Source
 */
class Paymenttype
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '1','label' => __('One Time')],
            ['value' => '2','label' => __('Split')],
            ['value' => '3','label' => __('Bonus')],
            ['value' => '4','label' => __('Bonus Split')],
            ['value' => '5','label' => __('Rebo')]
        ];
    }

    /**
     * @param $type
     * @return string
     */
    public function getPayType($type)
    {
        foreach ($this->toOptionArray() as $data) {
            if ($data['value'] == $type) {
                return $data['label'];
            }
        }

        return '';
    }
}
