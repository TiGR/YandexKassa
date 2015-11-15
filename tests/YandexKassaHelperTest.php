<?php

namespace polosatus\YandexKassa;

class YandexKassaHelperTest extends \PHPUnit_Framework_TestCase
{
    private $shopId = 12345;
    private $shopPassword = 'Pass12345';

    public function testGetters()
    {
        $helper = $this->getHelper(YandexKassaHelper::ACTION_CHECK);

        $this->assertEquals(YandexKassaHelper::ACTION_CHECK, $helper->getAction());
        $this->assertInstanceOf(Payment::class, $helper->getPayment());
    }

    public function testResponseBuilder()
    {
        $xml = new \SimpleXMLElement(
            $this->getHelper()->buildResponse(YandexKassaHelper::STATUS_PAYMENT_REJECTED, 'foo', 'bar')
        );

        $this->assertEquals('checkOrderResponse', $xml->getName());

        $date = \DateTime::createFromFormat(YandexKassaHelper::DATETIME_FORMAT, $xml['performedDatetime']);

        $this->assertInstanceOf(\DateTime::class, $date);

        $this->assertEquals(YandexKassaHelper::STATUS_PAYMENT_REJECTED, (string)(string)$xml['code']);
        $this->assertEquals($this->getFixtureData()['invoiceId'], (string)$xml['invoiceId']);
        $this->assertEquals($this->getFixtureData()['shopId'], (string)$xml['shopId']);
        $this->assertEquals('foo', (string)$xml['message']);
        $this->assertEquals('bar', (string)$xml['techMessage']);
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
        $this->getHelper(null, [], YandexKassaHelper::SECURITY_PKCS7);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Bad Yandex Kassa security mode: 42
     */
    public function testSecurityModeValidation()
    {
        $this->getHelper(null, [], 42);
    }

    /**
     * @expectedException \polosatus\YandexKassa\Exception\BadRequestException
     * @expectedExceptionMessage Got wrong shopId. Expected: 12345, got: 42
     */
    public function testShopIdValidation()
    {
        $this->getHelper(null, ['shopId' => 42]);
    }

    /**
     * @expectedException \polosatus\YandexKassa\Exception\BadRequestException
     * @expectedExceptionMessage Missing required fields: paymentDatetime
     */
    public function testAvisoActionValidation()
    {
        $this->getHelper(YandexKassaHelper::ACTION_AVISO);
    }

    /**
     * @expectedException \polosatus\YandexKassa\Exception\BadRequestException
     * @expectedExceptionMessage Unexpected action value: foo
     */
    public function testActionValidation()
    {
        $this->getHelper('foo');
    }

    /**
     * @expectedException \polosatus\YandexKassa\Exception\AuthorizationErrorException
     */
    public function testMD5CheckValidation()
    {
        $this->getHelper(null, ['md5' => 0]);
    }

    /**
     * @param $action
     * @param array|null $defaults
     * @param int $securityMode
     * @return YandexKassaHelper
     */
    private function getHelper($action = null, $defaults = [], $securityMode = YandexKassaHelper::SECURITY_MD5)
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

        return new YandexKassaHelper($this->shopId, $this->shopPassword, $data, $securityMode);
    }

    private function getFixtureData()
    {
        return [
            'shopId' => $this->shopId,
            'shopArticleId' => '9876543210',
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
        ];
    }
}
