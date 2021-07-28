<?php
namespace Veriteworks\Gmo\Model\Source;

/**
 * Class Splittype
 * @package Veriteworks\Gmo\Model\Source
 */
class Splittype
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
                ['value' => 2, 'label' => __('2Times')],
                ['value' => 3, 'label' => __('3Times')],
                ['value' => 5, 'label' => __('5Times')],
                ['value' => 6, 'label' => __('6Times')],
                ['value' => 10, 'label' => __('10Times')],
                ['value' => 12, 'label' => __('12Times')],
                ['value' => 15, 'label' => __('15Times')],
                ['value' => 18, 'label' => __('18Times')],
                ['value' => 20, 'label' => __('20Times')],
                ['value' => 24, 'label' => __('24Times')]
                ];
    }

    /**
     * @param $split
     * @return string
     */
    public function getSplitTime($split)
    {
        foreach ($this->toOptionArray() as $data) {
            if ($data['value'] == $split) {
                return $data['label'];
            }
        }

        return '';
    }
}
