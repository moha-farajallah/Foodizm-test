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

use Ksolves\Deliverydate\Helper\Time;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

/**
 * Class StartTime
 */
class StartTime extends Select
{
    /**
     * @var Time
     */
    private $timeHelper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Time $timeHelper
     * @param array $data
     */
    public function __construct(
        Context $context, 
        Time $timeHelper, 
        array $data = []
    ){
        parent::__construct($context, $data);
        $this->timeHelper = $timeHelper;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->timeHelper->getTimeDropdown());
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
