<?php
/**
 * Ksolves
 *
 * @category    Ksolves
 * @package     Ksolves_Deliverydate
 * @copyright   Copyright (c) Ksolves (https://www.ksolves.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php
 */

namespace Ksolves\Deliverydate\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const KSOLVES_CONFIG_MODULE_PATH = 'ks_deliverydate';

    protected $storeManager;
    /**
     * @type \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;
    /**
     * @var \Magento\Backend\App\Config
     */
    protected $backendConfig;
    /**
     * @var array
     */
    protected $isArea = [];
    /**
     * AbstractData constructor.
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager
    )
    {
        $this->objectManager = $objectManager;
        $this->storeManager  = $storeManager;
        parent::__construct($context);
    }
    
    /**
     * @param $field
     * @param null $scopeValue
     * @param string $scopeType
     * @return array|mixed
     */
    public function getConfigValue($field, $scopeValue = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        if (!$this->isArea() && is_null($scopeValue)) {
            /** @var \Magento\Backend\App\Config $backendConfig */
            if (!$this->backendConfig) {
                $this->backendConfig = $this->objectManager->get('Magento\Backend\App\ConfigInterface');
            }
            return $this->backendConfig->getValue($field);
        }
        return $this->scopeConfig->getValue($field, $scopeType, $scopeValue);
    }

    /**
     * @param $ver
     * @return mixed
     */
    public function versionCompare($ver)
    {
        $productMetadata = $this->objectManager->get(ProductMetadataInterface::class);
        $version         = $productMetadata->getVersion(); //will return the magento version
        return version_compare($version, $ver, '>=');
    }

    /**
     * @param string $code
     * @param null $storeId
     * @return mixed
     */
    public function getConfigGeneral($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';
        return $this->getConfigValue(static::KSOLVES_CONFIG_MODULE_PATH . '/general' . $code, $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     * @return mixed
     */
    public function getConfigTimeSlot($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';
        return $this->getConfigValue(static::KSOLVES_CONFIG_MODULE_PATH . '/time_slot' . $code, $storeId);
    }

    /**
     * @param string $code
     * @param null $storeId
     * @return mixed
     */
    public function getConfigHoliday($code = '', $storeId = null)
    {
        $code = ($code !== '') ? '/' . $code : '';
        return $this->getConfigValue(static::KSOLVES_CONFIG_MODULE_PATH . '/holiday' . $code, $storeId);
    }
    /**
     * @param null $store
     *
     * @return bool
     */
    public function isModuleDisabled($store = null)
    {
        return !$this->isEnabled($store);
    }

     /**
     * Is Admin Store
     *
     * @return bool
     */
    public function isAdmin()
    {
        return $this->isArea(Area::AREA_ADMINHTML);
    }
    /**
     * @param string $area
     * @return mixed
     */
    public function isArea($area = Area::AREA_FRONTEND)
    {
        if (!isset($this->isArea[$area])) {
            /** @var \Magento\Framework\App\State $state */
            $state = $this->objectManager->get('Magento\Framework\App\State');
            try {
                $this->isArea[$area] = ($state->getAreaCode() == $area);
            } catch (\Exception $e) {
                $this->isArea[$area] = false;
            }
        }
        return $this->isArea[$area];
    }

    /**
     * @param $data
     * @return string
     * @throws \Zend_Serializer_Exception
     */
    public function serialize($data)
    {
        if ($this->versionCompare('2.2.0')) {
            return self::jsonEncode($data);
        }
        return $this->getSerializeClass()->serialize($data);
    }
    /**
     * @param $string
     * @return mixed
     * @throws \Zend_Serializer_Exception
     */
    public function unserialize($string)
    {
        if ($this->versionCompare('2.2.0')) {
            return self::jsonDecode($string);
        }
        return $this->getSerializeClass()->unserialize($string);
    }
    /**
     * Encode the mixed $valueToEncode into the JSON format
     *
     * @param mixed $valueToEncode
     * @return string
     */
    public static function jsonEncode($valueToEncode)
    {
        try {
            $encodeValue = self::getJsonHelper()->jsonEncode($valueToEncode);
        } catch (\Exception $e) {
            $encodeValue = '{}';
        }
        return $encodeValue;
    }

    /**
     * Decodes the given $encodedValue string which is
     * encoded in the JSON format
     *
     * @param string $encodedValue
     * @return mixed
     */
    public static function jsonDecode($encodedValue)
    {
        try {
            $decodeValue = self::getJsonHelper()->jsonDecode($encodedValue);
        } catch (\Exception $e) {
            $decodeValue = [];
        }
        return $decodeValue;
    }


    /**
     * @param $path
     * @return mixed
     */
    public function getObject($path)
    {
        return $this->objectManager->get($path);
    }

    /**
     * @return \Magento\Framework\Json\Helper\Data|mixed
     */
    public static function getJsonHelper()
    {
        return ObjectManager::getInstance()->get(JsonHelper::class);
    }
    
    /**
     * @return \Zend_Serializer_Adapter_PhpSerialize|mixed
     */
    protected function getSerializeClass()
    {
        return $this->objectManager->get(\Zend_Serializer_Adapter_PhpSerialize::class);
    }


    ####################################### Get Configuration Value ######################################
    /**
     * @param null $storeId
     * @return bool
    */
    public function isEnabled($storeId = null)
    {
        return $this->getConfigGeneral('ksolves_deliverydate_general_deliverydate_enable', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
    */
    public function getDeliveryLabel($storeId = null)
    {
        return $this->getConfigGeneral('ksolves_deliverydate_general_deliverydate_label', $storeId);
    }

    /**
     * @param null $storeId
     * @return bool
    */
    public function isEnabledDeliveryTimeSlot($storeId = null)
    {
        return !!$this->getConfigGeneral('ksolves_deliverydate_general_deliverytimeslot_enable', $storeId);
    }

    /**
     * @param null $storeId
     * @return string
    */
    public function getDeliveryTimeSlotLabel($storeId = null)
    {
        return $this->getConfigGeneral('ksolves_deliverydate_general_deliverytimeslot_label', $storeId);
    }

    /**
     * Delivery Comment
     *
     * @param null $store
     *
     * @return bool
     */
    public function isEnabledDeliveryComment($store = null)
    {
        return !!$this->getConfigGeneral('ksolves_deliverydate_general_deliverycomment_enable', $store);
    }

    /**
     * Delivery Comment Label
     *
     * @param null $store
     *
     * @return string
     */
    public function getDeliveryCommentLabel($store = null)
    {
        return $this->getConfigGeneral('ksolves_deliverydate_general_deliverycomment_label', $store);
    }

    /**
     * Show the today Date
     *
     * @param null $store
     *
     * @return bool
     */
    public function getToadyDate($store = null)
    {
        return $this->getConfigGeneral('ksolves_show_today_date', $store);
    }

    /**
     * Date Format
     *
     * @param null $store
     *
     * @return string
     */
    public function getDateFormat($store = null)
    {
        $deliveryTimeFormat = $this->getConfigGeneral('ksolves_deliverydate_date_format', $store);

        return $deliveryTimeFormat ?: \Ksolves\Deliverydate\Model\Adminhtml\Source\DeliveryTime::KSOLVES_FULL_FORM;
    }


    /**
     * get Today Date
     *
     * @param null $date
     *
     * @return string
     */
    public function todayDay($date)
    {
        switch ($date) {
            case '-1':
                return 'No Day';
                break;
            case '0':
                return 'Sunday';
                break;
            case '1':
                return 'Monday';
                break;
            case '2':
                return 'Tuesday';
                break;
            case '3':
                return 'Wednesday';
                break;
            case '4':
                return 'Thursday';
                break;
            case '5':
                return 'Friday';
                break;
            case '6':
                return 'Saturday';
                break;                            
        }
    }

    /**
     * get the final Delivery Date 
     *
     * @param null $store
     *
     * @return array
     */

    public function getFinalDisableDateOption($date)
    {
        $todayDay          = date('l', strtotime($date));
        $disableDateOption = $this->getDisableTimeSlot();
        $_dates = array();
        foreach ($disableDateOption as $_value) {
            $disable_day      = $this->todayDay($_value['disable_day']);
            if ($disable_day === $todayDay) {
                $disable_timeSlot = $_value['add_start_time'].'-'.$_value['add_end_time'];
                if (!isset($disable_timeSlot)) continue;
                array_push($_dates,$disable_timeSlot);
            }
        }
        return $_dates;
    }

    /**
     * get the Time Slot Option 
     *
     * @param null $store
     *
     * @return array
     */

    public function getAddTimeSlotOption()
    {
        $disableDateOption = $this->getAddTimeSlot();

        $_dates = array();
        foreach ($disableDateOption as $_value) {
            $disable_timeSlot = $_value['add_start_time'].'-'.$_value['add_end_time'];
            if (!isset($disable_timeSlot)) continue;
            array_push($_dates,$disable_timeSlot);
        }
        return $_dates;
    }


    /**
     * Add Time Slot
     *
     * @param null $store
     *
     * @return bool|mixed
     */
    public function getFinalAddTimeSlotOption($_date)
    {
        $getAddTimeSlotOption = $this->getAddTimeSlotOption();
        $getFinalDisableDateOption = $this->getFinalDisableDateOption($_date);
        $_result = array_diff($getAddTimeSlotOption, $getFinalDisableDateOption);

        return $_result;

    }


    /**
     * Add Time Slot
     *
     * @param null $store
     *
     * @return bool|mixed
     */
    public function getAddTimeSlot($store = null)
    {
        return $this->unserialize($this->getConfigTimeSlot('ksolves_deliverydate_calendar_timeslot_add_timeslot', $store));
    }

    /**
     * Disable Time Slot
     *
     * @param null $store
     *
     * @return bool|mixed
     */
    public function getDisableTimeSlot($store = null)
    {
        return $this->unserialize($this->getConfigTimeSlot('ksolves_deliverydate_calendar_timeslot_disable_timeslot', $store));
    }


    /**
     * Days Off
     *
     * @param null $store
     *
     * @return bool|mixed
     */
    public function getDaysOff($store = null)
    {
        return $this->getConfigHoliday('ksolves_deliverydate_holiday_dayoff', $store);
    }

    /**
     * Date Off
     *
     * @param null $store
     *
     * @return mixed
     * @throws \Zend_Serializer_Exception
     */
    public function getDateOff($store = null)
    {
       return $this->unserialize($this->getConfigHoliday('ksolves_deliverydate_holiday_singleday_off', $store));
       
    }
    
    /**
     * Days Off
     *
     * @param null $store
     *
     * @return bool|mixed
     */
    public function getRawExcludeDates($store = null)
    {
        return $this->getConfigHoliday('ksolves_deliverydate_holiday_excludedates', $store);
    }
    

    /**
     * Returns excluded dates
     * @return array
     */
    public function getExcludeDates()
    {
        $raw = $this->getRawExcludeDates();
        if (empty($raw)) return [];
        if (! $values = $this->unserialize($raw)) return [];
        $dates = array();
        foreach ($values as $value) {
            if (!isset($value['date'])) continue;
            array_push($dates,$value['date']);
        }
        return $dates;
    }
}
