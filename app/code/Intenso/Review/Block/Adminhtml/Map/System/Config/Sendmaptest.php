<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

/**
 * Adminhtml send MAP test block
 */
namespace Intenso\Review\Block\Adminhtml\Map\System\Config;

class Sendmaptest extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Send MAP test Label
     *
     * @var string
     */
    protected $mapBtnLabel = 'Send Test';

    /**
     * Set MAP Test Button Label
     *
     * @param string $mapBtnLabel
     * @return \Intenso\Review\Block\Adminhtml\Map\System\Config\Sendmaptest
     */
    public function setMapButtonLabel($mapBtnLabel)
    {
        $this->mapBtnLabel = $mapBtnLabel;
        return $this;
    }

    /**
     * Set template to itself
     *
     * @return \Intenso\Review\Block\Adminhtml\Map\System\Config\Sendmaptest
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Intenso_Review::system/config/sendmaptest.phtml');
        }
        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->mapBtnLabel;

        if ($this->_request->getParam('store')) {
            $storeId = $this->_request->getParam('store');
        }

        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl(
                    'intenso_review/system_config_sendtestmap/sendtest',
                    ['_secure' => true, 'store' => $storeId]
                ),
            ]
        );

        return $this->_toHtml();
    }
}
