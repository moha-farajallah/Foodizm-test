<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */
namespace Intenso\Review\Block\Adminhtml\Maplog\Grid\Renderer;

/**
 * Adminhtml MAP grid item renderer for date
 */
class SentDate extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Render review date
     *
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getSentDate() === '0000-00-00 00:00:00') {
            return '-';
        }
        return $row->getSentDate();
    }
}
