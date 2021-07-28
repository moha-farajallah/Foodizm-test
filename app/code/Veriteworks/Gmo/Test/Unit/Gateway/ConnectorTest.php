<?php
namespace Veriteworks\Gmo\Test\Unit\Gateway;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Class ConnectorTest
 * @package Veriteworks\Gmo\Test\Unit\Gateway
 */
class ConnectorTest extends TestCase
{
    /**
     * @var \Veriteworks\Gmo\Gateway\Connector|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_connector;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeMock;

    /**
     *
     */
    protected function setUp() :void
    {
        $objectManager = new ObjectManager($this);

        $this->_scopeMock =
            $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
                ->disableOriginalConstructor()
                ->getMock();

        $adapter = new \Zend_Http_Client_Adapter_Test();

        $this->_connector =
            $objectManager->getObject(
                \Veriteworks\Gmo\Gateway\Connector::class,
                [
                    'adapter'=> $adapter,
                    'scopeConfig' => $this->_scopeMock
                ]
            );
    }

    /**
     *
     */
    public function testGetAdapter()
    {
        $adapter = $this->_connector->getAdapter();

        $this->assertContains('Zend_Http_Client_Adapter_Interface', class_implements($adapter));
    }

    /**
     *
     */
    public function testSetParam()
    {
        $res = $this->_connector->setParam('testparam', 'testvalue');
        $this->assertSame($res, $this->_connector);
    }

    /**
     *
     */
    public function testGetParam()
    {
        $this->_connector->setParam('testparam', 'testvalue');
        $value = $this->_connector->getParam('testparam');
        $this->assertEquals('testvalue', $value);
    }

    /**
     *
     */
    public function testCardEntryTran()
    {
        $params = [
            'OrderId' => '1000000',
            'JobCd' => 'AUTH',
            'Amount' => 1000,
            'ItemCode' => '000990',
            'TdFlag' => '0',
            'TdTenantName' => 'TestStore',
            'Site_ID' => '013456789',
            'Site_Password' => '0123456789'
        ];

        $this->_scopeMock->expects($this->once())
            ->method('getValue')
            ->with('veritegmo/common/gateway_url', 'store', null)
            ->willReturn('https://pt01.mul-pay.jp/');

        $this->_connector->setApiPath('EntryTran');

        foreach ($params as $key => $value) {
            $this->_connector->getParam($key, $value);
        }

        $this->_connector->getAdapter()
            ->setResponse($this->_getDummyCardAuthResponse());

        $result = $this->_connector->execute();
        $this->assertArrayHasKey('AccessID', $result);
    }

    /**
     * @return string
     */
    protected function _getDummyCardAuthResponse()
    {
        $response = "HTTP/1.1 200 OK"         . "\r\n" .
            "Content-Type: text/plain;charset=Windows-31J" . "\r\n" .
            "\r\n" .
            'AccessID=12345678987654322345678&AccessPass=012345678901234567890';
        return $response;
    }
}
