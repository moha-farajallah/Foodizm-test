<?php

namespace Scrumwheel\PaymentCost\Block\Adminhtml\System\Form\Field;

class PaymentFee extends  \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    protected $_columns = array();


    protected $_typeRenderer;

    protected $_feeRenderer;
    protected $_activeRenderer;

    protected $_searchFieldRenderer;

    protected $activeMethods;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Scrumwheel\PaymentCost\Model\Config\ActiveMethods $activeMethods,
        array $data = array()
    )
    {
        $this->activeMethods = $activeMethods;
        parent::__construct($context, $data);
    }


    protected function _prepareToRender()
    {
        $this->_typeRenderer        = null;
        $this->_feeRenderer        = null;
        $this->_activeRenderer        = null;
        $this->_searchFieldRenderer = null;

        $this->addColumn(
            'payment_method',
            [
                'label' => __('Payment Method'),
                'class' => 'required-entry validateDuplicateValue',
                'renderer' => $this->_getPaymentRenderer()]
        );
        $this->addColumn(
            'calculate_fee',
            ['label' => _('Calculate Handling fee'), 'renderer' => $this->_getCalculateFeeRenderer()]
        );
        $this->addColumn(
            'is_active',
            ['label' => _('Is Active'), 'renderer' => $this->_getIsActiveRenderer()]
        );

        $this->addColumn('fee',
            [
                'label' => __('Fee'),
                'class' => 'required-entry',
                'style' => 'width:70px'
            ]
        );
        $this->_addAfter       = false;
    }


    protected function _getPaymentRenderer() {

        if (!$this->_typeRenderer) {
            $this->_typeRenderer = $this->getLayout()->createBlock(
                'Scrumwheel\PaymentCost\Block\Adminhtml\System\Form\Field\Methods',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_typeRenderer->setClass('payemtfee_select');
        }
        return $this->_typeRenderer;
    }

    protected function _prepareArrayRow(\Magento\Framework\DataObject $row) {

        $optionExtraAttr = array();

        $optionExtraAttr['option_' . $this->_getPaymentRenderer()->calcOptionHash($row->getData('payment_method'))] =
                'selected="selected"';

        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }

    protected function _getCalculateFeeRenderer(){
        if (!$this->_feeRenderer) {
            $this->_feeRenderer = $this->getLayout()->createBlock(
                'Scrumwheel\PaymentCost\Block\Adminhtml\System\Form\Field\Calculatefee',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_feeRenderer->setClass('calculate_select');
        }
        return $this->_feeRenderer;
    }

    protected function _getIsActiveRenderer(){
        if (!$this->_activeRenderer) {
            $this->_activeRenderer = $this->getLayout()->createBlock(
                'Scrumwheel\PaymentCost\Block\Adminhtml\System\Form\Field\Isactive',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_activeRenderer->setClass('active_select');
        }
        return $this->_activeRenderer;
    }
}
