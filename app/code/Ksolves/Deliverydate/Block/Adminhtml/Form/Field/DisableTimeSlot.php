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
use Ksolves\Deliverydate\Block\Adminhtml\Form\Dropdown\Days;
use Ksolves\Deliverydate\Block\Adminhtml\Form\Dropdown\StartTime;
use Magento\Framework\DataObject;
/**
 * Class DisableTimeSlot
 */
class DisableTimeSlot extends AbstractFieldArray
{
    /**
     * @var Days
     */
    protected $weekendDaysRenderer = null;

    /**
     * @var StartTime
     */
    protected $startTimeRenderer = null;

     /**
     * @var StartTime
     */
    protected $endTimeRenderer = null;

    /**
     * Returns renderer for starting time dropdown element
     *
     * @return mixed
     */
    protected function getStartTimeRenderer()
    {
        if (!$this->startTimeRenderer) {
            $this->startTimeRenderer = $this->getLayout()->createBlock(
                StartTime::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->startTimeRenderer;
    }

    /**
     * Returns renderer for ending time dropdown element
     *
     * @return mixed
     */
    protected function getEndTimeRenderer()
    {
        if (!$this->endTimeRenderer) {
            $this->endTimeRenderer = $this->getLayout()->createBlock(
                StartTime::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->endTimeRenderer;
    }

    /**
     * Returns renderer for weekend days element
     *
     * @return void
     */
    protected function getWeekendDaysRenderer()
    {
        if (!$this->weekendDaysRenderer) {
            $this->weekendDaysRenderer = $this->getLayout()->createBlock(
                Days::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->weekendDaysRenderer;
    }

    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'disable_day',
            [
                'label'     => __('Day'),
                'size'      => '100px',
                'class'     => 'required-entry',
                'renderer'  => $this->getWeekendDaysRenderer(),
            ]
        );

        $this->addColumn(
            'add_start_time',
            [
                'label'     => __('Start Time'),
                'size'      => '100px',
                'class'     => 'required-entry',
                'renderer'  => $this->getStartTimeRenderer(),
            ]
        );
        $this->addColumn(
            'add_end_time',
            [
                'label' => __('End Time'),
                'size'      => '100px',
                'class'     => 'required-entry',
                'renderer'  => $this->getEndTimeRenderer(),
            ]
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Rule');
    }

    /**
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $disableDay = $row->getDisableDay();
        $options = [];
        if ($disableDay) {
            $options['option_' . $this->getWeekendDaysRenderer()->calcOptionHash($disableDay)]
                = 'selected="selected"';

            $addStartTime = $row->getAddStartTime();
            $options['option_' . $this->getStartTimeRenderer()->calcOptionHash($addStartTime)]
                    = 'selected="selected"';

            $addEndTime = $row->getAddEndTime();
            $options['option_' . $this->getEndTimeRenderer()->calcOptionHash($addEndTime)]
                    = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }
}
