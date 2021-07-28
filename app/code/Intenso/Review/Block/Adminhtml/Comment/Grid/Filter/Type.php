<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
namespace Intenso\Review\Block\Adminhtml\Comment\Grid\Filter;

/**
 * Adminhtml review grid filter by type
 */
class Type extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * Get grid options
     *
     * @return array
     */
    protected function _getOptions()
    {
        return [
            ['label' => '', 'value' => ''],
            ['label' => __('Customer'), 'value' => 1],
            ['label' => __('Guest'), 'value' => 2]
        ];
    }

    /**
     * Get condition
     *
     * @return int
     */
    public function getCondition()
    {
        if ($this->getValue() == 1) {
            return 1;
        } else {
            return 2;
        }
    }
}
