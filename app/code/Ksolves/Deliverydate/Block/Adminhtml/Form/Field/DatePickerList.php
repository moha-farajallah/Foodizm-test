<?php
/**
 * Ksolves
 *
 * @category    Ksolves
 * @package     Ksolves_Deliverydate
 * @copyright   Copyright (c) Ksolves (https://www.ksolves.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php
 */

 
namespace Ksolves\Deliverydate\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;

 /**
 * Class DatePickerList
 */
class DatePickerList extends AbstractFieldArray
{
    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('date', 
            [
                'label' => __('Date'), 
                'class' => 'js-date-excluded-datepicker required-entry'
            ]);

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Date');
        parent::_prepareToRender();
    }
 
    /**
     * Prepare existing row data object
     * Convert backend date format "2018-01-12" to front format "12/01/2018"
     *
     * @param DataObject $row
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $key = 'date';
        if (!isset($row[$key])) return;
        $rowId = $row['_id'];
        try {
            $sourceDate = \DateTime::createFromFormat('Y/m/d', $row[$key]);
            $renderedDate = $sourceDate->format('d/m/Y');
            $row[$key] = $renderedDate;
            $columnValues = $row['column_values'];
            $columnValues[$this->_getCellInputElementId($rowId, $key)] = $renderedDate;
            $row['column_values'] = $columnValues;
        } catch (\Exception $e) {
            // Just skipping error values
        }
    }
 
    /**
     * Get the grid and scripts contents
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $html = parent::_getElementHtml($element);
 
        $script = <<<JS
            <script type="text/javascript">
                // Bind click to "Add" buttons and bind datepicker to added date fields
                require(["jquery", "jquery/ui"], function (jq) {
                    jq(function(){
                        function bindDatePicker() {
                            setTimeout(function() {
                                jq(".js-date-excluded-datepicker").datepicker( { dateFormat: "dd/mm/yy" } );
                            }, 50);
                        }
                        bindDatePicker();
                        jq("button.action-add").on("click", function(e) {
                            bindDatePicker();
                        });
                    });
                });
            </script>
JS;
        $html .= $script;
        return $html;
    }
 
}