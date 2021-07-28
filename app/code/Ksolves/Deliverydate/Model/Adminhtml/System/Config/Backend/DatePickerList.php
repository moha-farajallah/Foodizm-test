<?php
/**
 * Ksolves
 *
 * @category    Ksolves
 * @package     Ksolves_Deliverydate
 * @copyright   Copyright (c) Ksolves (https://www.ksolves.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php
 */

namespace Ksolves\Deliverydate\Model\Adminhtml\System\Config\Backend;
 
 /**
 * Class DatePickerList
 */
class DatePickerList extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
{
    /**
     * On save convert front value format like "12/01/2018" to backend format "2018-01-12"
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = [];
        $values = $this->getValue();
        foreach ((array)$values as $key => $data) {
            if ($key == '__empty') continue;
            if (!isset($data['date'])) continue;
            try {
                $date = \DateTime::createFromFormat('d/m/Y', $data['date']);
                $value[$key] = [
                    'date' => $date->format('Y/m/d')
                ];
            } catch (\Exception $e) {
                // Just skipping error values
            }
        }
        $this->setValue($value);
        return parent::beforeSave();
    }
}