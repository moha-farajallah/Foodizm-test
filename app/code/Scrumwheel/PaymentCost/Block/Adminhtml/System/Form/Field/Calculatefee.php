<?php

namespace Scrumwheel\PaymentCost\Block\Adminhtml\System\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class Calculatefee extends Select
{
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
            $this->setExtraParams('style="width:110px;"');
        }
        return parent::_toHtml();
    }

    private function getSourceOptions()
    {
        return [
            ['label' => 'Fixed','value' => 'F'],
            ['label' => 'Percent','value' => 'P']
        ];
    }
}
