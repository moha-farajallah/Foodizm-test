<?php

namespace Scrumwheel\PaymentCost\Block\Adminhtml\System\Form\Field;

use Magento\Framework\View\Element\Html\Select;

class Isactive extends Select
{
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function setInputId($value)
    {
        return $this->setId($value);
    }

    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
            $this->setExtraParams('style="width:90px;"');
        }
        return parent::_toHtml();
    }

    private function getSourceOptions()
    {
        return [
            ['label' => 'Yes','value' => '1'],
            ['label' => 'No','value' => '0']
        ];
    }
}
