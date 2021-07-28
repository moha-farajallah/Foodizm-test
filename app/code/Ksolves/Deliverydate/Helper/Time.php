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

use Ksolves\Deliverydate\Model\Adminhtml\Source\Time as TimeSource;

/**
 * Class Time
 */
class Time
{
    /**
     * All possible credit card types
     *
     * @var array
     */
    private $timeDropdown = [];

    /**
     * @var \Ksolves\Deliverydate\Model\Adminhtml\Source\Time
     */
    private $timeSource;

    /**
     * @param Time $timeSource
     */
    public function __construct(TimeSource $timeSource)
    {
        $this->timeSource = $timeSource;
    }

    /**
     * All possible credit card types
     *
     * @return array
     */
    public function getTimeDropdown()
    {
        if (!$this->timeDropdown) {
            $this->timeDropdown = $this->timeSource->toOptionArray();
        }
        return $this->timeDropdown;
    }
}
