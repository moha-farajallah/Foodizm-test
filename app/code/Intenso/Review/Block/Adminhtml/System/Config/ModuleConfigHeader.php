<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class ModuleConfigHeader
 */
class ModuleConfigHeader extends Fieldset
{
    /**
     * Render the html of the element
     *
     * @param AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(AbstractElement $element)
    {
        $html = '<div class="intenso-module-config-header" >Advanced Reviews</div>';

        return $html;
    }
}
