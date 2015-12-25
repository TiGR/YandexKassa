<?php

namespace TiGR\YandexKassa;

use TiGR\YandexKassa\Exception\AuthorizationErrorException;
use TiGR\YandexKassa\Exception\BadRequestException;

class YandexKassaHelperTest extends \PHPUnit_Framework_TestCase
{
    private $shopId = 12345;
    private $shopPassword = 'Pass12345';

    public function testGetters()
    {
        $helper = $this->getHelper(YandexKassaHelper::ACTION_CHECK);

        $this->assertEquals(YandexKassaHelper::ACTION_CHECK, $helper->getAction());
        $this->assertInstanceOf(__NAMESPACE__.'\\Payment', $helper->getPayment());
    }

    public function testResponseBuilder()
    {
        $data = $this->getFixtureData();

        $xml = new \SimpleXMLElement(
            $this->getHelper()->buildResponse(YandexKassaHelper::STATUS_PAYMENT_REJECTED, 'foo', 'bar')
        );

        $this->assertEquals('checkOrderResponse', $xml->getName());

        $date = \DateTime::createFromFormat(YandexKassaHelper::DATETIME_FORMAT, $xml['performedDatetime']);

        $this->assertInstanceOf('\\DateTime', $date);

        $this->assertEquals(YandexKassaHelper::STATUS_PAYMENT_REJECTED, (string)$xml['code']);
        $this->assertEquals($data['invoiceId'], (string)$xml['invoiceId']);
        $this->assertEquals($data['shopId'], (string)$xml['shopId']);
        $this->assertEquals('foo', (string)$xml['message']);
        $this->assertEquals('bar', (string)$xml['techMessage']);

        $xml = new \SimpleXMLElement(
            $this->getHelper()->buildResponse()
        );

        $this->assertEquals(YandexKassaHelper::STATUS_SUCCESS, (string)$xml['code']);

        $helper = $this->getHelper();
        try {
            $helper->parseRequest(
                array('md5' => 'foobar', 'action' => YandexKassaHelper::ACTION_CHECK) + $this->getFixtureData()
            );
        } catch (AuthorizationErrorException $e) {
        }
        $xml = new \SimpleXMLElement(
            $helper->buildResponse()
        );

        $this->assertEquals(YandexKassaHelper::STATUS_AUTHORIZATION_ERROR, (string)$xml['code']);

        $helper = $this->getHelper();
        try {
            $helper->parseRequest(array('invoiceId' => '100500'));
        } catch (BadRequestException $e) {
        }
        $xml = new \SimpleXMLElement(
            $helper->buildResponse()
        );

        $this->assertEquals(YandexKassaHelper::STATUS_BAD_REQUEST, (string)$xml['code']);
        $this->assertEquals('100500', (string)$xml['invoiceId']);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Bad status code provided: 42
     */
    public function testStatusValidation()
    {
        $this->getHelper()->buildResponse(42);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Yandex Kassa certificate security mode not implemented
     */
    public function testPKCS7NotImplemented()
    {
        $this->getHelper(null, array(), YandexKassaHelper::SECURITY_PKCS7);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Bad Yandex Kassa security mode: 42
     */
    public function testSecurityModeValidation()
    {
        $this->getHelper(null, array(), 42);
    }

    /**
     * @expectedException \TiGR\YandexKassa\Exception\BadRequestException
     * @expectedExceptionMessage Got wrong shopId. Expected: 12345, got: 42
     */
    public function testShopIdValidation()
    {
        $this->getHelper(null, array('shopId' => 42));
    }

    /**
     * @expectedException \TiGR\YandexKassa\Exception\BadRequestException
     * @expectedExceptionMessage Missing required fields: paymentDatetime
     */
    public function testAvisoActionValidation()
    {
        $this->getHelper(YandexKassaHelper::ACTION_AVISO);
    }

    /**
     * @expectedException \TiGR\YandexKassa\Exception\BadRequestException
     * @expectedExceptionMessage Unexpected action value: foo
     */
    public function testActionValidation()
    {
        $this->getHelper('foo');
    }

    /**
     * @expectedException \TiGR\YandexKassa\Exception\BadRequestException
     * @expectedExceptionMessage No action provided
     */
    public function testEmptyActionValidation()
    {
        $helper = new YandexKassaHelper($this->shopId, $this->shopPassword);
        $data = $this->getFixtureData();
        unset($data['action']);
        $helper->parseRequest($data);
    }

    /**
     * @expectedException \TiGR\YandexKassa\Exception\AuthorizationErrorException
     */
    public function testMD5CheckValidation()
    {
        $this->getHelper(null, array('md5' => 0));
    }

    /**
     * @param $action
     * @param array|null $defaults
     * @param int $securityMode
     * @return YandexKassaHelper
     */
    private function getHelper($action = null, $defaults = array(), $securityMode = YandexKassaHelper::SECURITY_MD5)
    {
        $defaults['action'] = ($action ? $action : YandexKassaHelper::ACTION_CHECK);

        $data = $defaults + $this->getFixtureData();

        if ($securityMode == YandexKassaHelper::SECURITY_MD5 and !isset($data['md5'])) {
            $data['md5'] = $str = strtoupper(
                md5(
                    $data['action'].";".$data['orderSumAmount'].";"
                    .$data['orderSumCurrencyPaycash'].";".$data['orderSumBankPaycash'].";".$data['shopId'].";"
                    .$data['invoiceId'].";".$data['customerNumber'].";".$this->shopPassword
                )
            );
        }

        $helper = new YandexKassaHelper($this->shopId, $this->shopPassword, $securityMode);
        $helper->parseRequest($data);

        return $helper;
    }

    private function getFixtureData()
    {
        return array(
            'shopId' => $this->shopId,
            'requestDatetime' => '2015-11-13T12:34:53.000-07:00',
            'invoiceId' => '234567890',
            'customerNumber' => 'user123',
            'paymentPayerCode' => '321',
            'paymentType' => '123',
            'orderCreatedDatetime' => '2015-11-13T12:34:56.000-07:00',
            'orderSumAmount' => 100.00,
            'orderSumCurrencyPaycash' => Sum::CURRENCY_RUB,
            'orderSumBankPaycash' => 1234,
            'shopSumAmount' => 100.00,
            'shopSumCurrencyPaycash' => Sum::CURRENCY_TEST,
            'shopSumBankPaycash' => 1000,
        );
    }
}
