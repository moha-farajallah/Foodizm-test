<?php
/**
 * Ksolves
 *
 * @category    Ksolves
 * @package     Ksolves_Deliverydate
 * @copyright   Copyright (c) Ksolves (https://www.ksolves.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php
 */
namespace Ksolves\Deliverydate\Block\Adminhtml\Form\Dropdown;

use Ksolves\Deliverydate\Helper\WeekendDays;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class Days
 */
class Days extends Select
{
    /**
     * @var WeekendDays
     */
    private $WeekendDaysHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param WeekendDays $WeekendDaysHelper
     * @param array $data
     */
    public function __construct(
        Context $context, 
        WeekendDays $WeekendDaysHelper, 
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->WeekendDaysHelper = $WeekendDaysHelper;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->WeekendDaysHelper->getWeekendDayDropdown());
        }
        return parent::_toHtml();
    }

    /**
     * Sets name for input element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }
}
