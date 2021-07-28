<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
namespace Intenso\Review\Block\Adminhtml\Comment\Grid\Renderer;

/**
 * Adminhtml review grid item renderer for item type
 */
class Type extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render review type
     *
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getCustomerId()) {
            return __('Customer');
        }
        return __('Guest');
    }
}
