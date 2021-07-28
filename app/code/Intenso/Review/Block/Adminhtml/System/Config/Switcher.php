<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Backend system config field renderer
 */
class Switcher extends Field
{
    /**
     * Retrieve Element HTML fragment
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $value = $element->getValue() ?: 0;
        $html = '<div class="admin__actions-switch" data-role="switcher_' . $element->getId() .
            '" data-bind="scope: \'switcher_' . $element->getId() . '\'">
                    <input type="hidden" name="' . $element->getName() . '"  value="0">
                    <input class="admin__actions-switch-checkbox"
                           type="checkbox"
                           id="' . $element->getId() . '"
                           value="' . $value . '"
                           onclick="this.dispatchEvent(new Event(\'change\'))"
                           data-bind="simpleChecked: checked, value: value, attr: { disabled: disabled, name: inputName }, hasFocus: focused">
                    <label class="admin__actions-switch-label" for="' . $element->getId() . '">
                        <span data-bind="attr: {
                                   \'data-text-on\': \'' . __('Yes') . '\',
                                   \'data-text-off\': \'' . __('No') . '\'
                              }"
                              class="admin__actions-switch-text"></span>
                    </label>
                </div>';

        if ($element->getDisabled()) {
            $html .= '<script>
                        require([
                            \'jquery\',
                            \'domReady\'
                        ], function ($, domReady) {
                            domReady(function () {
                                // Toggle the field availability, if element is disabled (depending on scope)
                                toggleValueElements(
                                    {checked: true},
                                    $(\'[data-role="switcher_' . $element->getId() . '"]\').parent()[0]
                                );
                            });
                        });
                    </script>';
        }

        $html .= '<script type="text/x-magento-init">
                    {
                        "[data-role=\'switcher_' . $element->getId() . '\']": {
                            "Magento_Ui/js/core/app": {
                                "components": {
                                    "switcher_' . $element->getId() . '": {
                                        "component": "Magento_Ui/js/form/element/single-checkbox",
                                        "dataScope": "' . $element->getName() . '",
                                        "value": ' . $value . ',
                                        "valueMap": {
                                            "false": 0,
                                            "true": 1
                                        }
                                    }
                                }
                            }
                        }
                    }
                    </script>';
        return $html;
    }
}
