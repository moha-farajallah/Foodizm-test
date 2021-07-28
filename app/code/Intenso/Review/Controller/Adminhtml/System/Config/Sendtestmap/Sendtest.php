<?php
/**
 * Copyright © Intenso Commerce. All rights reserved.
 * See License.txt for license details.
 */

namespace Intenso\Review\Controller\Adminhtml\System\Config\Sendtestmap;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Intenso\Review\Controller\Adminhtml\System\Config\Sendtestmap as Sendtestmap;

class Sendtest extends Sendtestmap
{
    /**
     * Customer sender email config path
     */
    const XML_PATH_CUSTOMER_EMAIL_SENDER = 'intenso_review/map_options/sender_email_identity';

    /**
     * MAP Template config path
     */
    const XML_PATH_MAP_TEMPLATE = 'intenso_review/map_options/map_template';

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        Session $authSession,
        Http $request
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->authSession = $authSession;
        $this->request = $request;
    }

    /**
     * Send Mail After Purchase test
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $email = $this->getRequest()->getParam('email');
        $storeId = $this->storeManager->getStore()->getId();

        if ($this->request->getParam('store')) {
            $storeId = $this->request->getParam('store');
        }

        // create object mockups for email template
        $mapMockup = new \Magento\Framework\DataObject();
        $mapMockup->setCustomerToken('test');
        $orderMockup = new \Magento\Framework\DataObject();
        $orderMockup->setCustomerFirstname($this->authSession->getUser()->getFirstname());
        $orderMockup->setCustomerLastname($this->authSession->getUser()->getLastname());
        $orderMockup->setCustomerEmail($email);
        $productMockup = new \Magento\Framework\DataObject();
        $productMockup->setName('{{product_name}}');

        // send MAP
        $transport = $this->transportBuilder->setTemplateIdentifier(
            $this->scopeConfig->getValue(
                self::XML_PATH_MAP_TEMPLATE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            )
        )->setTemplateOptions(
            [
                'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                'store' => $storeId
            ]
        )->setTemplateVars(
            [
                'map' => $mapMockup,
                'order' => $orderMockup,
                'product' => $productMockup,
                'productUrl' => $this->storeManager->getStore()->getBaseUrl(),
                'store' => $this->storeManager->getStore($storeId)
            ]
        )->setFrom(
            $this->scopeConfig->getValue(
                self::XML_PATH_CUSTOMER_EMAIL_SENDER,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        )->addTo(
            $email
        )->getTransport();

        try {
            $transport->sendMessage();
            return $resultJson->setData([
                'valid' => 1,
                'message' => __('The test email has been sent')
            ]);
        } catch (\Exception $e) {
            return $resultJson->setData([
                'valid' => 0,
                'message' => $e->getMessage()
            ]);
        }
    }
}
