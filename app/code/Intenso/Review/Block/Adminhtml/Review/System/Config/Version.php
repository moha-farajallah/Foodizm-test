<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Block\Adminhtml\Review\System\Config;

use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Backend system config version field renderer
 */
class Version extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Data helper
     *
     * @var \Intenso\Review\Helper\Data
     */
    protected $dataHelper = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Intenso\Review\Helper\Data $dataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Intenso\Review\Helper\Data $dataHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataHelper = $dataHelper;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $version = $this->dataHelper->getVersion();
        return '<span style="display: inline-block; padding-top: 7px;">' . $version . '<span>';
    }
}
