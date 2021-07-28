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

use Ksolves\Deliverydate\Model\Adminhtml\Source\WeekDay as WeekDays;

/**
 * Class WeekendDays
 */
class WeekendDays
{
    /**
     * All possible credit card types
     *
     * @var array
     */
    private $WeekendDayDropdown = [];

    /**
     * @var \Ksolves\Deliverydate\Model\Adminhtml\Source\WeekDay
     */
    private $weekDay;

    /**
     * @param WeekDay $weekDay
     */
    public function __construct(WeekDays $weekDay)
    {
        $this->weekDay = $weekDay;
    }

    /**
     * All possible weekend days
     *
     * @return array
     */
    public function getWeekendDayDropdown()
    {
        if (!$this->WeekendDayDropdown) {
            $this->WeekendDayDropdown = $this->weekDay->toOptionArray();
        }
        return $this->WeekendDayDropdown;
    }
}
