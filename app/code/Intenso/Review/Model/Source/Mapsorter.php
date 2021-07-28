<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
namespace Intenso\Review\Model\Source;

/**
 * Class Mapsorter
 * @codeCoverageIgnore
 */
class Mapsorter implements \Magento\Framework\Option\ArrayInterface
{
    const MAP_SORTER_PRICE    = 'price';
    const MAP_SORTER_REVIEWS  = 'reviews';

    /**
     * Possible orders
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::MAP_SORTER_PRICE,
                'label' => __('Most Expensive'),
            ],
            [
                'value' => self::MAP_SORTER_REVIEWS,
                'label' => __('Least Reviewed')
            ]
        ];
    }
}
