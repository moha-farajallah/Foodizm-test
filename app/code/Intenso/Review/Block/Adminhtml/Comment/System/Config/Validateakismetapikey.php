<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

/**
 * Adminhtml Akismet API Key validation block
 */
namespace Intenso\Review\Block\Adminhtml\Comment\System\Config;

class Validateakismetapikey extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Akismet API Key Field
     *
     * @var string
     */
    protected $akismetApiKey = 'intenso_review_akismet_api_key';

    /**
     * Validate API Key Label
     *
     * @var string
     */
    protected $akismetBtnLabel = 'Validate API Key';

    /**
     * Set Akismet API Key Field
     *
     * @param string $apiKeyField
     * @return \Intenso\Review\Block\Adminhtml\Comment\System\Config\Validateakismetapikey
     */
    public function setAkismetApiKeyField($apiKeyField)
    {
        $this->akismetApiKey = $apiKeyField;
        return $this;
    }

    /**
     * Get Akismet API Key Field
     *
     * @return string
     */
    public function getAkismetApiKeyField()
    {
        return $this->akismetApiKey;
    }

    /**
     * Set Validate API Key Button Label
     *
     * @param string $akismetBtnLabel
     * @return \Intenso\Review\Block\Adminhtml\Comment\System\Config\Validateakismetapikey
     */
    public function setAkismetButtonLabel($akismetBtnLabel)
    {
        $this->akismetBtnLabel = $akismetBtnLabel;
        return $this;
    }

    /**
     * Set template to itself
     *
     * @return \Intenso\Review\Block\Adminhtml\Comment\System\Config\Validateakismetapikey
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('Intenso_Review::system/config/validateakismetapikey.phtml');
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
        $originalData = $element->getOriginalData();
        $buttonLabel = !empty($originalData['button_label']) ? $originalData['button_label'] : $this->akismetBtnLabel;
        $this->addData(
            [
                'button_label' => __($buttonLabel),
                'html_id' => $element->getHtmlId(),
                'ajax_url' => $this->_urlBuilder->getUrl('intenso_review/system_config_validateakismet/validate'),
            ]
        );

        return $this->_toHtml();
    }
}
